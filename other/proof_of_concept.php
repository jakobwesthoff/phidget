<?php
//
// WARNING: This is just a proof of concept, that something like desktop
//          widgets are possible with php-gtk. This app is not supposed
//          to do anything useful.
//

// Function called if the window needs to be redrawn
function OnExpose( $window, $event ) 
{
    $gdkwindow = $event->window;
    $gc = new GdkGC( $gdkwindow );
    $cmap = $gdkwindow->get_colormap();

    $gc->set_foreground( $cmap->alloc_color( '#000000' ) );
    $gdkwindow->draw_rectangle( $gc, true, 0, 0, 200, 200 );

    $gc->set_foreground( $cmap->alloc_color( '#fcaf3e' ) );    
    $gdkwindow->draw_rectangle( $gc, true, 25, 25, 150, 150 );
}

// Create new GtkWindow and connect destroy event
$window = new GtkWindow();
$window->connect_simple( 'destroy', array( 'gtk', 'main_quit' ) );

// Remove the window borders
$window->set_decorated( false );

// Hide the window from the taskbar an the pager
$window->set_skip_pager_hint( true );
$window->set_skip_taskbar_hint( true );

// Display it on every virtual desktop
$window->stick();

// Set its size and center it on screen
$window->resize( 200,200 );
$window->set_position( Gtk::WIN_POS_CENTER );

// The window should be always below all others
$window->set_keep_below( true );
$window->set_type_hint( Gdk::WINDOW_TYPE_HINT_DOCK );

// We want to handle the draw event ourselfs
$window->set_app_paintable( true );

// Register the event fired on painting
$window->connect( 'expose-event', 'OnExpose' );

// Show the window
$window->show_all();

// Enter the gtk main loop
Gtk::Main();

?>
