<?php
/**
 * jdWidgetFinder 
 * 
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net> 
 * @author Manuel Pichler <mp@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinder extends jdWidget
{
    protected $background;
    protected $items;
    protected $size;

    protected $lastMouseX;
    protected $lastMouseY;

    public function __construct( SimpleXMLElement $configuration )
    {
        parent::__construct( $configuration );
        
        // Initialize class members
        $this->items = array();        
        $this->lastMouseX = 0;
        $this->lastMouseY = 0;

        //Calculate needed size
        $realwidth = 0;
	    foreach ( $this->items as $item )
	    {
	        $realwidth += $item->size;
	    }
        
        //@todo: this is not always correct. Calculate the maximum needed size here.
        
        /* Needed width is calculated as follows:
         * sum( icon size ) + ( ( number of icons ) - 1 ) * ( space between icons ) + ( maximum icon size )
         * Needed height is calculated as follows:
         * At the moment this is just the maximum icon size
         */
        $this->size = array(
            $realwidth + ( count( $this->items ) - 1 ) * (int) $this->configuration->space + (int) $this->configuration->zoom,
            (int) $this->configuration->zoom
        );
        
        // Make local properties
        $iconZoom  = (int) $this->configuration->zoom;
        $iconSize  = (int) $this->configuration->size;
        $iconSpace = (int) $this->configuration->space;
                
        // Calculate x and y offsets
        $xoffset = $iconSize + ( $iconSpace * ( ( $iconZoom - $iconSize ) / ( $iconSpace * 2 ) ) );
        $yoffset = $iconZoom - round( $iconSize / 2.0 );

        foreach ( $this->configuration->items->item as $itemconfig ) 
        {
            // Ask factory for new item instance
	        $item = jdWidgetFinderItem::createItem(
	                                        $this->configuration, 
	                                        $itemconfig,
	                                        $xoffset,
	                                        $yoffset );
	                                        
            // Check for none standard item width
	        if ( $item->size !== $iconSize ) 
	        {
	            // Reset item x offset
	            $item->x = $item->x + ( ( $item->size - $iconSize ) / 2 );
	        }

            // Calculate next x offset
            $xoffset += $item->size + $iconSpace;
            
            $this->items[] = $item;
        } 

        // Connect to the needed signals
        $this->add_events( 
            Gdk::BUTTON_PRESS_MASK
           |Gdk::POINTER_MOTION_MASK
           |Gdk::LEAVE_NOTIFY_MASK
        );
        $this->connect( "button-press-event", array( $this, "OnMousePress" ) );
        $this->connect( "motion-notify-event", array( $this, "OnMouseMove" ) );
        $this->connect( "leave-notify-event", array( $this, "OnMouseLeave" ) );
        
        $this->background = new jdWidgetFinderBackground( 
                                    (string) $this->configuration->background,
                                    $this->size[0], 
                                    $this->size[1],
                                    $iconSize
                                );
    }

    protected function getSize()
    {
        return $this->size;
    }

    public function OnExpose( $gc, $window )
    {
        // DEBUG: Draw border around the widget
        $cmap = $window->get_colormap();
        $gc->set_foreground( $cmap->alloc_color( "#000000" ) );
        $window->draw_rectangle( $gc, false, 0, 0, $this->size[0] - 1, $this->size[1] - 1 );
        
        $this->background->draw( $gc, $window );
        
        // Draw every icon to the widget surface
        foreach( $this->items as $item ) 
        {
            // Draw the icon
            $item->draw( $gc, $window );
        }
    }

    public function OnMousePress( jdWidget $source, GdkEvent $event ) 
    {   
        // Difference between event x and widget x
        $diff = PHP_INT_MAX;
        // The clicked icon instance
        $clickedIcon = null;

        // Find the matching icon
        foreach ( $this->items as $item ) 
        {
            $tmp = abs( $event->x - $item->x );
            if ( $tmp < $diff ) 
            {
                $diff = $tmp;
                $clickedIcon = $item;
            }
        }
        
        if ( $clickedIcon !== null ) 
        {
            // Check for left button click
            if ( $event->button === 1 )
            {
                $clickedIcon->doLeftClick( $source->window );
	        } 
	        else if ( $event->button === 3 ) 
	        {
	            $clickedIcon->doRightClick( $source->window );
	        }
//            new jdWidgetFinderIconAction( $clickedIcon, $source->window );
        }
    }

    public function OnMouseMove( jdWidget $source, GdkEvent $event ) 
    {
        if ( $this->lastMouseX === $event->x
           && $this->lastMouseY === $event->y ) 
        {
            // No change needed, because the mouse posistion did not change
            return;
        }

        $this->lastMouseX = $event->x;
        $this->lastMouseY = $event->y;

        // Calculate the center of the first icon
        $xoffset = round( $this->configuration->zoom * 0.75 );

        $realwidth = 0;
        // Scale all icons
        // xoffset is center base
        foreach ( $this->items as $item )
        {
            $scalefactor = $this->calculateScaling( $xoffset, $event->x, $event->y ); // Calculate the new size and y position
            $item->size = (int) $this->configuration->size * $scalefactor;
            $item->y    = (int) $this->configuration->zoom - round( $item->size / 2.0 ) - ( 1.5 * $scalefactor );

            $xoffset   += (int) $item->size + (int) $this->configuration->space;
            $realwidth += $item->size + (int) $this->configuration->space;
        }
        
        $realwidth -= (int) $this->configuration->space;

        // Calc new xoffset based on the real width of the bar
        $xoffset = round( ( $this->size[0] - $realwidth ) / 2.0 );
        // Correct the overlapping positions and center the bar correctly
        // xoffset is left border based
        foreach ( $this->items as $item ) 
        {
            $item->x = $xoffset + round( $item->size / 2.0 );
            $xoffset += $item->size + (int) $this->configuration->space;
        }
        // @todo: check if redraw is really neccessary
        // Redraw the widget
        $source->window->invalidate_rect(
            new GdkRectangle(
                0,
                0,
                $this->size[0],
                $this->size[1]
            ),
            false
        );
    }
    
    public function OnMouseLeave( jdWidget $source, GdkEvent $event ) 
    {
        // Delegete leave event to mouse move, it contains the logic.
        $this->OnMouseMove( $source, $event );
    }

    protected function calculateScaling( $center, $mouseX, $mouseY ) 
    {
        if ( $mouseX <= 0 
          || $mouseY <= 0
          || $mouseX >= $this->size[0]
          || $mouseY >= $this->size[1] ) 
        {
            // No scaling here, the mouse left the widget.
            return 1.0;
        }
        
        $multiplierX = (int) $this->configuration->zoomoffset - abs( $center - $mouseX );
        $multiplierY = (int) $this->configuration->zoomoffset - abs( $this->configuration->zoom - $mouseY );
        $multiplier = round( ( ( $multiplierX * (int) $this->configuration->zoom ) + $multiplierY ) / (int) $this->configuration->zoom );

        if ( $multiplier < 0 ) 
        {
            return 1.0;
        }
        
        //@todo: something is definetly wrong with the scaling factor calculation
        //       I will fix this tommorow I am just to tired now
        $scalefactor = ( ( (float) $multiplier * ( ( (float) $this->configuration->zoom - (float) $this->configuration->size ) / (float) $this->configuration->zoomoffset ) ) / (float) $this->configuration->size );
        $scalefactor = ( 1.0 + ( ( $scalefactor / $this->configuration->zoomoffset) * $multiplierY ) );
        
        return $scalefactor;
    }
}
