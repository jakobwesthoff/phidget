<?php
/**
 * jdWidgetFinderClockItem
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderClockItem extends jdWidgetFinderItem
{

    protected $window = null;

    /**
     * Constructor takes the configuration and the item size as argument.
     *
     * @param SimpleXMLElement $configuration
     * @param integer $size
     */
    public function __construct( SimpleXMLElement $configuration, $size )
    {
        parent::__construct(  $configuration, $size );

        // Set up icon item properties
        $this->properties["pixbuf"]  = GdkPixbuf::new_from_file( (string) $configuration->background );

        // TODO: Use a system property
        date_default_timezone_set( "Europe/Berlin" );
    }

    public function draw( GdkGC $gc, GdkWindow $window )
    {
        // Keep owner window
        if ( $this->window === null )
        {
            $this->window = $window;
        }

        // Scale this pixbuf
        $pixbuf = $this->pixbuf->scale_simple( $this->width, $this->height, Gdk::INTERP_HYPER );

        // Draw current pixbuf
        $window->draw_pixbuf( $gc, $pixbuf, 0, 0, $this->x, $this->y );

        // Free resource
        unset( $pixbuf );

        $cmap = $window->get_colormap();
        $color = $cmap->alloc_color( "#444444" );

        $gc->set_foreground( $color );

        // Get current time values
        list( $h, $i , $s ) = explode( ":", date( "h:i:s" ) );

        // Calculate pointer x/y center
        $offsetY = ( $this->y + ( $this->height * 0.5 ) );
        $offsetX = ( $this->x + ( $this->width * 0.5 ) );

        // Draw hours
        list( $x, $y ) = $this->calculateXY( ( $h * 5 ) + ( $i / 12 ), ( $this->width * 0.5 ) );
        $window->draw_line( $gc, $offsetX, $offsetY, $x, $y );
        $window->draw_line( $gc, $offsetX + 1, $offsetY + 1, $x, $y );

        // Draw minutes
        list( $x, $y ) = $this->calculateXY( $i, ( $this->width * 0.7 ) );
        $window->draw_line( $gc, $offsetX, $offsetY, $x, $y );
        $window->draw_line( $gc, $offsetX + 1, $offsetY + 1, $x, $y );

        // New color for seconds
        $color = $cmap->alloc_color( "#ff0000" );
        $gc->set_foreground( $color );


        // Draw seconds
        list( $x, $y ) = $this->calculateXY( $s, ( $this->width * 0.8 ) );
        $window->draw_line( $gc, $offsetX, $offsetY, $x, $y );

        // Add gtk timer
        Gtk::timeout_add( 1000, array( $this, "updateClock" ) );
    }

    public function updateClock()
    {
        $this->window->invalidate_rect(
            new GdkRectangle(
                $this->x,
                $this->y,
                $this->width,
                $this->height
             ), false
        );
    }

    protected function calculateXY( $time, $size )
    {
        $b  = $size / 2;
        $al = $time * 6;
        $be = 90;
        $ga = ( 180 - $be - $al );

        return array(
            round( ( $this->x + ( $this->width * 0.5 ) ) + ( ( $b * sin( deg2rad( $al ) ) ) / sin( deg2rad( $be ) ) ) ),
            round( ( $this->y + ( $this->height * 0.5 ) ) - ( ( $b * sin( deg2rad( $ga ) ) ) / sin( deg2rad( $be ) ) ) )
        );
    }

}
