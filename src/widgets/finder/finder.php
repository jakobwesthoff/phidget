<?php
/**
 * jdWidgetFinder 
 * 
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff. All rights reserved.
 * @author Jakob Westhoff <jakob@php.net> 
 * @license GPL
 */
class jdWidgetFinder extends jdWidget
{
    protected $background;
    protected $icons;
    protected $size = null;

    protected $lastMouseX;
    protected $lastMouseY;

    public function __construct( SimpleXMLElement $configuration )
    {
        parent::__construct( $configuration );
        
        // Initialize all class members
        $this->icons = array();
        $this->lastMouseX = 0;
        $this->lastMouseY = 0;
        
        // Make local properties
        $icozoom  = (int) $this->configuration->zoom;
        $icosize  = (int) $this->configuration->size;
        $icospace = (int) $this->configuration->space;
                
        // Calculate x and y offsets
        $xoffset = $icosize + ( $icospace * ( ( $icozoom - $icosize ) / ( $icospace * 2 ) ) );
        $yoffset = $icozoom - round( $icosize / 2.0 );

        foreach ( $this->configuration->items->item as $itemconfig ) 
        {
            // Ask factory for new item instance
	        $item = jdWidgetFinderItem::createItem(
	                                        $this->configuration, 
	                                        $itemconfig,
	                                        $xoffset,
	                                        $yoffset );
	                                        
            // Check for none standard item width
	        if ( $item->size !== $icosize ) 
	        {
	            // Reset item x offset
	            $item->x = $item->x + ( ( $item->size - $icosize ) / 2 );
	        }

            // Calculate next x offset
            $xoffset += $item->size + $icospace;
            
            $this->icons[] = $item;
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
        
        $size = $this->getSize();
        
        $this->background = new jdWidgetFinderBackground( 
                                    (string) $this->configuration->background,
                                    $size[0], 
                                    $size[1],
                                    $icosize
                                );
    }

    protected function getSize()
    {
        // Check for previous size calculation
        if ( $this->size !== null )
        {
            return $this->size;
        }
        
        // Sum width of all items 
        $itemwidth = 0;
	    foreach ( $this->icons as $item )
	    {
	        $itemwidth += $item->size;
	    }
        
        /* Needed width is calculated as follows:
         * sum( icon size ) + ( ( number of icons ) - 1 ) * ( space between icons ) + ( maximum icon size )
         * Needed height is calculated as follows:
         * At the moment this is just the maximum icon size
         */
        $this->size = array(
            $itemwidth + ( count( $this->icons ) - 1 ) * (int) $this->configuration->space + (int) $this->configuration->zoom,
            (int) $this->configuration->zoom
        );
        
        return $this->size;
    }

    public function OnExpose( $gc, $window )
    {
        $size = $this->getSize();

        // DEBUG: Draw border around the widget
        $cmap = $window->get_colormap();
        $gc->set_foreground( $cmap->alloc_color( "#000000" ) );
        $window->draw_rectangle( $gc, false, 0, 0, $size[0] - 1, $size[1] - 1 );
        
        $this->background->draw( $gc, $window );
        
        // Draw every icon to the widget surface
        foreach( $this->icons as $icon ) 
        {
            // Draw the icon
            $icon->draw( $gc, $window );
        }
    }

    public function OnMousePress( jdWidget $source, GdkEvent $event ) 
    {   
        // Difference between event x and widget x
        $diff = PHP_INT_MAX;
        // The clicked icon instance
        $clickedIcon = null;

        // Find the matching icon
        foreach ( $this->icons as $icon ) 
        {
            $tmp = abs( $event->x - $icon->x );
            if ( $tmp < $diff ) 
            {
                $diff = $tmp;
                $clickedIcon = $icon;
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

        $size = $this->getSize();
        
        // Calculate the center of the first icon
        $xoffset = round( $this->configuration->zoom * 0.75 );

        $realwidth = 0;
        // Scale all icons
        // xoffset is center base
        foreach ( $this->icons as $icon )
        {
            $scalefactor = $this->calculateScaling( $xoffset, $event->x, $event->y ); // Calculate the new size and y position
            $icon->size = (int) $this->configuration->size * $scalefactor;
            $icon->y    = (int) $this->configuration->zoom - round( $icon->size / 2.0 ) - ( 1.5 * $scalefactor );

            $xoffset   += (int) $icon->size + (int) $this->configuration->space;
            $realwidth += $icon->size + (int) $this->configuration->space;
        }
        
        $realwidth -= (int) $this->configuration->space;

        // Calc new xoffset based on the real width of the bar
        $xoffset = round( ( $size[0] - $realwidth ) / 2.0 );
        // Correct the overlapping positions and center the bar correctly
        // xoffset is left border based
        foreach ( $this->icons as $icon ) 
        {
            $icon->x = $xoffset + round( $icon->size / 2.0 );
            $xoffset += $icon->size + (int) $this->configuration->space;
        }
        // @todo: check if redraw is really neccessary
        // Redraw the widget
        $size = $this->getSize();
        $source->window->invalidate_rect(
            new GdkRectangle(
                0,
                0,
                $size[0],
                $size[1]
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
        
        $size = $this->getSize();
        
        if ( $mouseX <= 0 
          || $mouseY <= 0
          || $mouseX >= $size[0]
          || $mouseY >= $size[1] ) 
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