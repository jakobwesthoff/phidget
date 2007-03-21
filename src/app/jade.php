<?php

require_once( 'config/config.php' );

$config = jdConfig::getInstance();

foreach( $config->widgets->widget as $widgetConfig ) 
{
    $class = 'jdWidget' . ucfirst( (string) $widgetConfig['type'] );
    new $class( $widgetConfig );
    echo "Added $class\n";
}

Gtk::Main();

?>
