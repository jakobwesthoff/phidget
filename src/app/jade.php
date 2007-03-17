<?php

require_once( 'config/config.php' );

$xml = simplexml_load_file( 'config/widgets.xml' );

$widgets = array();

foreach( $xml->widget as $widgetConfig ) 
{
    $class = 'jdWidget' . ucfirst( (string) $widgetConfig['type'] );
    $widget = new $class( $widgetConfig );
    $widget->initWidget();
    echo "Added $class\n";
}

Gtk::Main();

?>
