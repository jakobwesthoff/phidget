<?php
/**
 * jdWidgetFinder 
 * 
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net> 
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinder extends jdWidget
{
    
    /**
     * The finder bar background.
     *
     * @type jdWidgetFinderBackground
     * @var jdWidgetFinderBackground $background
     */
    protected $background;
    
    /**
     * <tt>Array</tt> of <tt>jdWidgetFinderItem</tt> instances.
     *
     * @type array<jdWidgetFinderItem>
     * @var array $items
     */
    protected $items;
    
    /**
     * <tt>Array</tt> with the calculated widget size.
     * 
     * <code>
     *   array(
     *       0  =>  (widget width),
     *       1  =>  (widget height)
     *   );
     * </code>
     * 
     * @type array<integer>
     * @var array $size
     */
    protected $size;

    /**
     * Mouse x offset from the last recieved event.
     *
     * @type integer
     * @var integer $lastMouseX
     */
    protected $lastMouseX;
    
    /**
     * Mouse y offset from the last recieved event.
     *
     * @type integer
     * @var integer $lastMouseY
     */
    protected $lastMouseY;

    /**
     * Main init method for a widget, this method is called from the
     * <tt>jdWidget</tt> constructor.
     *
     */
    protected function init()
    {
        // Initialize class members
        $this->items = array();        
        $this->lastMouseX = 0;
        $this->lastMouseY = 0;

        // Make local properties
        $itemZoom  = (int) $this->configuration->zoom;
        $itemSize  = (int) $this->configuration->size;
        $itemSpace = (int) $this->configuration->space;
                
        // Calculate x and y offsets
        $xoffset = $itemSize + ( $itemSpace * ( ( $itemZoom - $itemSize ) / ( $itemSpace * 2 ) ) );
        $yoffset = $itemZoom - round( $itemSize / 2.0 );

        foreach ( $this->configuration->items->item as $itemconfig ) 
        {
            // Ask factory for new item instance
	        $item = jdWidgetFinderItem::createItem(
	                                        $this->configuration, 
	                                        $itemconfig,
	                                        $xoffset,
	                                        $yoffset 
                                        );
	                                        
            // Check for none standard item width
	        if ( $item->size !== $itemSize ) 
	        {
	            // Reset item x offset
	            $item->x = $item->x + round( ( $item->size - $itemSize ) / 2 );
	        }

            // Calculate next x offset
            $xoffset += $item->size + $itemSpace;
            
            $this->items[] = $item;
        } 

        //Calculate the size of the complete widget
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
            $realwidth + ( ( count( $this->items ) - 1 ) * $itemSpace ) + $itemZoom,
            $itemZoom
        );
        
        // Connect to the needed signals
        $this->add_events( 
            Gdk::BUTTON_PRESS_MASK
           |Gdk::POINTER_MOTION_MASK
           |Gdk::LEAVE_NOTIFY_MASK
        );
        $this->connect( "button-press-event", array( $this, "OnMousePress" ) );
        $this->connect( "motion-notify-event", array( $this, "OnMouseMove" ) );
        $this->connect( "leave-notify-event", array( $this, "OnMouseLeave" ) );
        
        // Instantiate the background class
        $this->background = new jdWidgetFinderBackground( 
                                    (string) $this->configuration->background,
                                    $this->size[0], 
                                    $this->size[1],
                                    $itemSize
                                );
    }

    /**
     * Returns the size of the widget implementation.
     * 
     * <code>
     *   array(
     *       0  =>  (widget width),
     *       1  =>  (widget height)
     *   );
     * </code>
     *
     * @return array
     */
    protected function getSize()
    {
        return $this->size;
    }

    /**
     * Main drawing method for a widget.
     *
     * @param GdkGC $gc
     * @param GdkWindow $window
     */
    public function OnExpose( GdkGC $gc, GdkWindow $window )
    {
        // DEBUG: Draw border around the widget
        $cmap = $window->get_colormap();
        $gc->set_foreground( $cmap->alloc_color( "#000000" ) );
        $window->draw_rectangle( $gc, false, 0, 0, $this->size[0] - 1, $this->size[1] - 1 );
        
        // Draw the background on the surface
        $this->background->draw( $gc, $window );
        
        // Draw every icon to the widget surface
        foreach( $this->items as $item ) 
        {
            $item->draw( $gc, $window );
        }
    }

    /**
     * Event handler for on mouse press events. This method is called for both
     * left and right button clicks.
     * 
     * @param jdWidget $source
     * @param GdkEvent $event 
     */
    public function OnMousePress( jdWidget $source, GdkEvent $event ) 
    {   
        // Difference between event x and widget x
        $lastOffset = PHP_INT_MAX;

        // Find the matching item
        foreach ( $this->items as $item ) 
        {
            if( $offset = abs( $event->x - $item->x ) >= $lastOffset || $item === $this->items[count( $this->items ) - 1] ) 
            {
                // We have found our item
                switch( $event->button ) 
                {
                    case 1:
                        $item->doLeftClick( $source->window );
                        break;
                        
                    case 3:
                        $item->doRightClick( $source->window );
                        break;
                }
                
                break;
            }

            $lastOffset = $offset;
        }
    }

    /**
     * Event handler for mouse move events. It calculates the different item
     * scalings and item positions.
     *
     * @param jdWidget $source
     * @param GdkEvent $event
     */
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
        // xoffset is center based
        foreach ( $this->items as $item )
        {
            $scalefactor    = $this->calculateScaling( $xoffset, $event->x, $event->y ); // Calculate the new size and y position
            $item->size     = (int) $this->configuration->size * $scalefactor;
            $item->y        = (int) $this->configuration->zoom - round( $item->size / 2.0 ) - ( 1.5 * $scalefactor );

            $xoffset       += (int) $item->size + (int) $this->configuration->space;
            $realwidth     += $item->size + (int) $this->configuration->space;
        }        
        // We added a space behin the last item, which isn't really there
        $realwidth -= (int) $this->configuration->space;

        // Calc new xoffset based on the new width of the bar
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
    
    /**
     * Event handler for mouse leave events. This method delegates to the 
     * <tt>OnMouseMove()</tt> method, it ensures zero scaling for quick mice 
     * movers ;)
     *
     * @param jdWidget $source
     * @param GdkEvent $event
     */
    public function OnMouseLeave( jdWidget $source, GdkEvent $event ) 
    {
        // Delegate leave event to mouse move, it contains the logic.
        $this->OnMouseMove( $source, $event );
    }

    /**
     * Calculates the scaling for the given <tt>$center</tt> in connection with
     * the given <tt>$mouseX</tt> and <tt>$mouseY</tt> value.
     *
     * @param integer $center Item center position
     * @param integer $mouseX
     * @param integer $mouseY
     * @return float The item scaling multiplier.
     */
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
        
        // Local properties for shorter calculation code
	    $zoomOffset = (float) $this->configuration->zoomoffset;
	    $itemSize   = (float) $this->configuration->size;
	    $itemZoom   = (float) $this->configuration->zoom;
        
        $multiplierX = $zoomOffset - abs( $center - $mouseX );
        $multiplierY = $zoomOffset - abs( $itemZoom - $mouseY );
        
        // The x multiplier has a greater influence  on item scaling, so
        // use the max zoom value for weight calculation.
        $multiplier = ( ( $multiplierX * ( $itemZoom / 2 ) ) + $multiplierY ) / ( $itemZoom / 2 );

        if ( $multiplier < 0 ) 
        {
            return 1.0;
        }
        
        $scalefactor = ( ( $multiplier * ( ( $itemZoom - $itemSize ) / $zoomOffset ) ) / $itemSize );
        $scalefactor = ( 1.0 + ( ( $scalefactor / $zoomOffset) * $multiplierY ) );
        
        return $scalefactor;
    }
}
