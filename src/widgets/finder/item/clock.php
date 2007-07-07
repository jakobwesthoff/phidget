<?php
/**
 * jdWidgetFinderClockItem
 *
 * @property-read GdkPixbuf $pixbuf The background image for the clock
 * item.
 * @property-read boolean $seconds Shall the clock show seconds?
 * @property-read integer $refresh The widget refresh rate. If seconds
 * are shown this will be a second otherwise we use a minute for refresh.
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
    /**
     * The context window for this item. This window is used for item
     * drawing.
     *
     * @type GdkWindow
     * @var GdkWindow $window
     */
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

        // Check for a second setting
        if ( isset( $configuration["seconds"] ) )
        {
            $this->properties["seconds"] = (string) $configuration["seconds"] === "true";
        }
        else
        {
            // No config, default is show seconds
            $this->properties["seconds"] = true;
        }

        // If we don't show seconds, refresh rate is one minute
        $this->properties["refresh"] = ( $this->seconds === true ? 1000 : 60000 );

        // TODO: Use a system property
        date_default_timezone_set( "Europe/Berlin" );

        // Start initial timer
        Gtk::timeout_add( $this->refresh, array( $this, "updateClock" ) );
    }

    /**
     * Main draw method for finder bar items.
     *
     * @param GdkGC $gc The currently used graphical context.
     * @param GdkWindow $window The drawable context for the item.
     */
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

        // Check for seconds
        if ( $this->seconds === true )
        {
            // New color for seconds
            $color = $cmap->alloc_color( "#ff0000" );
            $gc->set_foreground( $color );

            // Draw seconds
            list( $x, $y ) = $this->calculateXY( $s, ( $this->width * 0.8 ) );
            $window->draw_line( $gc, $offsetX, $offsetY, $x, $y );
        }
    }

    /**
     * Clock timer callback method. This method will request a redraw
     * for the clock if this widget was drawn once.
     */
    public function updateClock()
    {
        if ( $this->window !== null )
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

        // Add gtk timer
        Gtk::timeout_add( $this->refresh, array( $this, "updateClock" ) );
    }

    /**
     * Helper method for the clock it calculates the x/y offset for the
     * pointers.
     *
     * <code>
     *   array(
     *     0  =>  x,  // The x offset
     *     1  =>  y,  // The y offset
     *   )
     * </code>
     *
     * @param integer $time The time value
     * @param integer $size The available pointer space.
     * @return array The x/y offset.
     */
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
