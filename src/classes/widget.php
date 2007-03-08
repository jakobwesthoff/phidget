<?php

abstract class jdWidget extends GtkWindow 
{

    protected $x;
    protected $y;
    protected $width;
    protected $height;
    protected $bgFilename;
    protected $bgPixbuf;

    public function __construct() 
    {
        // Call the parent constructor
        parent::__construct();

        // Connect destroy event
        $this->connect_simple( 'destroy', array( 'gtk', 'main_quit' ) );

        // Remove the window borders
        $this->set_decorated( false );

        // Hide the window from the taskbar an the pager
        $this->set_skip_pager_hint( true );
        $this->set_skip_taskbar_hint( true );

        // Display it on every virtual desktop
        $this->stick();

        // Set its size and center it on screen
        $this->resize( 200,200 );
        $this->set_position( Gtk::WIN_POS_CENTER );

        // The window should be always below all others
        $this->set_keep_below( true );
        $this->set_type_hint( Gdk::WINDOW_TYPE_HINT_DOCK );

        // We want to handle the draw event ourselfs
        $this->set_app_paintable( true );

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

    public function configure_event( $window, $event ) 
    {
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

    public function expose_event( $window, $event ) 
    {
        $gdkwindow = $event->window;
        $gc = new GdkGC( $gdkwindow );
        
        // Draw the pseudo transparency background
        $gdkwindow->draw_pixbuf( $gc, $this->bgPixbuf, $this->x, $this->y, 0, 0, $this->width, $this->height );

        return $this->OnExpose( $gc, $gdkwindow );
    }

    public abstract function OnExpose( $gc, $window );
}

?>