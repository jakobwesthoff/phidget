<?php

require_once( 'config/config.php' );

$xml = simplexml_load_file( 'config/widgets.xml' );

$widgets = array();

foreach( $xml->widget as $widget ) 
{
    $class = 'jdWidget' . ucfirst( (string) $widget['type'] );
    $widgets[] = new $class( $widget );
}

Gtk::Main();

?>
