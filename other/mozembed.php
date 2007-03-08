<?php
    
    define( 'BASEURI', 'http://google.de/' );

    function open_uri( $moz, $uri ) 
    {
        if ( $uri == BASEURI ) 
        {
            return false;
        } 
        else
        {
            shell_exec( 'firefox "' . $uri .'" &' );
            return true;
        }
    }

    $window = new GtkWindow();

    // Connect destroy event
    $window->connect_simple( 'destroy', array( 'gtk', 'main_quit' ) );

    // Remove the window borders
    $window->set_decorated( false );

    // Hide the window from the taskbar an the pager
    $window->set_skip_pager_hint( true );
    $window->set_skip_taskbar_hint( true );

    // Display it on every virtual desktop
    $window->stick();

    // Set its size and center it on screen
    $screen = Gdk::get_default_root_window()->get_screen();
    $window->resize( $screen->get_width(), $screen->get_width() );
    $window->set_position( Gtk::WIN_POS_CENTER );

    // The window should be always below all others
    $window->set_keep_below( true );

    // Add mozembed
    $moz = new GtkMozEmbed();
    $moz->connect( 'open-uri', 'open_uri' );
    $window->add( $moz );
    $moz->load_url( BASEURI );

    // Show the window
    $window->show_all();

    Gtk::Main();

?>
