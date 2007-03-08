<?php

define( "BASEDIR", dirname( __FILE__ ) . '/..' );

ini_set( 
    "include_path", 
    BASEDIR
);

require_once( 'classes/base.php' );

jdBase::addAutoloadDirectory( dirname( __FILE__ ). '/../autoload' );

function __autoload( $classname )
{
    // PEAR autoloading
    if ( strpos( $classname, "_" ) !== false )
    {
        require_once str_replace( "_", "/", $classname ) . ".php";
    }
    else 
    {
        jdBase::autoload( $classname );
    }
}

?>
