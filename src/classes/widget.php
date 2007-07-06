<?php
/**
 * jdWidget
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
abstract class jdWidget extends GtkWindow
{

    protected $x;
    protected $y;
    protected $width;
    protected $height;
    protected $bgFilename;
    protected $bgPixbuf;
    protected $configuration;

    /**
     * Class constructor takes the widget configuration as argument.
     *
     * @param SimpleXMLElement $configuration
     */
    public final function __construct( SimpleXMLElement $configuration )
    {
        // Call the parent constructor
        parent::__construct();

        $this->configuration = $configuration;

        // Initialize the widget
        $this->init();

        // Do all the necessary gtk stuff to display the widget on
        // the desktop

        // Connect destroy event
        $this->connect_simple( 'destroy', array( 'gtk', 'main_quit' ) );

        // Remove the window borders
        $this->set_decorated( false );

        // Hide the window from the taskbar an the pager
        $this->set_skip_pager_hint( true );
        $this->set_skip_taskbar_hint( true );

        // Display it on every virtual desktop
        $this->stick();

        // Where should we display the window, above or below al contents
        if ( isset( $configuration["top"] ) && (string) $configuration["top"] === "true" )
        {
            $this->set_keep_above( true );
        }
        else
        {
            $this->set_keep_below( true );
        }

        // We want to handle the draw event ourselfs
        $this->set_app_paintable( true );

        // Set its size and position on the screen
        $size = $this->getSize();
        $this->move( (int) $this->configuration['x'], (int) $this->configuration['y'] );
        $this->resize( $size[0], $size[1] );

        // Register the event fired on movement and resizing
        $this->connect( 'configure-event', array( $this, 'configure_event' ) );

        // Register the event fired on painting
        $this->connect( 'expose-event', array( $this, 'expose_event' ) );

        // Retrieve the current background image
        $this->bgFilename = trim( shell_exec( 'gconftool-2 --get /desktop/gnome/background/picture_filename' ) );

        // Load the background and scale it to the desktop size
        $screen = Gdk::get_default_root_window()->get_screen();
        $this->bgPixbuf = GdkPixbuf::new_from_file_at_size( $this->bgFilename, $screen->get_width(), $screen->get_height() );

        // Show the window
        $this->show_all();
    }

    public function configure_event( jdWidget $window, GdkEvent $event )
    {
        // Just repaint if the widget position or size has changed.
        if ( $this->x !== $event->x
           || $this->y !== $event->y
           || $this->height !== $event->height
           || $this->width !== $event->width )
        {
            $this->x = $event->x;
            $this->y = $event->y;
            $this->height = $event->height;
            $this->width = $event->width;
            $this->window->invalidate_rect( new GdkRectangle( 0, 0, $this->width, $this->height ), true );
        }
    }

    public final function expose_event( jdWidget $window, GdkEvent $event )
    {
        $gdkwindow = $event->window;
        $gc = new GdkGC( $gdkwindow );

        // Draw the pseudo transparency background
        $gdkwindow->draw_pixbuf( $gc, $this->bgPixbuf, $this->x + $event->area->x, $this->y + $event->area->y, $event->area->x, $event->area->y, $event->area->width, $event->area->height );

        return $this->OnExpose( $gc, $event );
    }

    public abstract function OnExpose( GdkGC $gc, GdkEvent $event );

    /**
     * Returns the size of the widget as an <tt>array</tt>.
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
    protected abstract function getSize();

    /**
     * Main init method for widget implementations. Use this for custom init
     * tasks.
     */
    protected abstract function init();

}
