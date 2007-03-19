<?php

require_once( 'config/config.php' );

$config = new jdBaseConfigLoader();

$widgets = array();

foreach( $config->widgets->widget as $widgetConfig ) 
{
    $class = 'jdWidget' . ucfirst( (string) $widgetConfig['type'] );
    $widget = new $class( $widgetConfig );
    echo "Added $class\n";
}

Gtk::Main();

?>
