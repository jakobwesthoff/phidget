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
    protected $icons;

    protected $lastMouseX;
    protected $lastMouseY;

    public function __construct( $configuration ) {
        // Constructor of the superclass
        parent::__construct( $configuration );
        
        // Initialize all class members
        $this->icons = array();
        $this->lastMouseX = 0;
        $this->lastMouseY = 0;
        
        // Initialize all icons
        $xoffset = round( $this->configuration->size / 2.0 );
        foreach ( $this->configuration->icons->icon as $filename ) 
        {
            $icon = new jdWidgetFinderIcon( (string) $filename );           
            $icon->size = (int) $this->configuration->size;
            $this->icons[] = $icon;
            echo "added Icon: ", ( string ) $filename, "\n";
        } 

        $size = $this->getSize();
        // Beginning xoffset left based (NOT CENTER BASED)
        $xoffset = round( ( $size[0] - ( count( $this->icons ) * (int) $this->configuration->size + ( count( $this->icons ) - 1 ) * (int) $this->configuration->space ) ) / 2.0 );
        // Correct the x,y positions of the icons
        foreach( $this->icons as $icon ) 
        {
            $icon->x = $xoffset + round( $icon->size / 2.0 );
            $icon->y = $size[1] - round( $icon->size / 2.0 );
            $xoffset += $icon->size + (int) $this->configuration->space;
        }

        // Connect to the needed signals
        $this->add_events( 
            Gdk::BUTTON_PRESS_MASK
           |Gdk::POINTER_MOTION_MASK
        );
        $this->connect( "button-press-event", array( $this, "OnMousePress" ) );
        $this->connect( "motion-notify-event", array( $this, "OnMouseMove" ) );
    }

    protected function getSize()
    {
        //@todo: change size calculation function, to reflect the real size of the bar. This one is not accurate
        //       anymore because of the zoomoffset config value
        /* Needed width is calculated as follows:
         * ( number of icons ) * ( icon size ) + ( ( number of icons ) - 1 ) * ( space between icons ) + ( maximum icon size )
         * Needed height is calculated as follows:
         * At the moment this is just the maximum icon size
         */
        return array(
            count( $this->icons ) * (int) $this->configuration->size + ( count( $this->icons ) - 1 ) * (int) $this->configuration->space + (int) $this->configuration->zoom,
            (int) $this->configuration->zoom
        );
    }

    public function OnExpose( $gc, $window )
    {
        $size = $this->getSize();

        // DEBUG: Draw border around the widget
        $cmap = $window->get_colormap();
        $gc->set_foreground( $cmap->alloc_color( "#000000" ) );
        $window->draw_rectangle( $gc, false, 0, 0, $size[0] - 1, $size[1] - 1 );
        
        // Draw every icon to the widget surface
        foreach( $this->icons as $icon ) 
        {
            // Draw the icon
            $icon->draw( $gc, $window );
        }
    }

    public function OnMousePress( jdWidget $source, GdkEvent $event ) 
    {
        //@todo: Determine which icon has been clicked. This should be easy by retrieving
        //       the biggest icon.
        echo "clicked!\n";
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

        $xoffset = round( ( $size[0] - ( count( $this->icons ) * (int) $this->configuration->size + ( count( $this->icons ) - 1 ) * (int) $this->configuration->space ) ) / 2.0 ) + round( $this->configuration->size / 2.0 );
        $mouseX = $event->x;
        
        $realwidth = 0;
        // Scale all icons
        // xoffset is center based
        foreach ( $this->icons as $icon )
        {
            $scalefactor = $this->calculateScaling( $xoffset, $mouseX );
            // Calculate the new size and y position
            $icon->size = (int) $this->configuration->size * $scalefactor;
            $icon->y = $size[1] - round( $icon->size / 2.0 );

            $xoffset += (int) $this->configuration->size + (int) $this->configuration->space;
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

    protected function calculateScaling( $center, $mouseX ) 
    {
        $multiplier = (int) $this->configuration->zoomoffset - abs( $center - $mouseX );
        if ( $multiplier < 0 ) 
        {
            return 1.0;
        }
        $scalefactor = ( 1.0 + ( ( (float) $multiplier * ( ( (float) $this->configuration->zoom - (float) $this->configuration->size ) / (float) $this->configuration->zoomoffset ) ) / (float) $this->configuration->size ) );
        return $scalefactor;
    }
}

