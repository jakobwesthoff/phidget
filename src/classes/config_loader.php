<?php
/**
 * jdBaseConfigLoader
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdBaseConfigLoader 
{
    
    /**
     * The name of the widget file name.
     */
    const CONFIG_WIDGET = "widgets.xml";
    
    /**
     * The name of the user configuration directory.
     */
    const CONFIG_LOCAL_DIR = ".phidgets";
    
    /**
     * Configuration properties.
     * 
     * @type array<SimpleXMLElement>
     * @var array $properties
     */
    protected $properties = array();
    
    /**
     * The simple xml configuration.
     * 
     * @type SimpleXMLElement
     * @var SimpleXMLElement $
     */
    public function __construct()
    {
	    // Generate local file name
	    $fileName = sprintf(
	                    "%s/%s/%s",
	                    getenv( "HOME" ),
	                    self::CONFIG_LOCAL_DIR,
	                    self::CONFIG_WIDGET
	                );
	                
        // Check for a local config
	    if ( !file_exists( $fileName ) )
	    {
	        // Switch to application config
	        $fileName = "config/widgets.xml";
	    }
	    
	    // Load configuration file(s)
	    $this->properties = array(
            "widgets"  =>  simplexml_load_file($fileName)
        );
    }

    /**
     * Overloaded function to retrieve the available properties
     * (Default behaviour: Everything not explicitedly denied will be allowed)
     *
     * @param mixed $key Property to retrieve
     * @return void
     */
    public function __get( $key )
    {
        switch( $key )
        {
            default:
                if ( !array_key_exists( $key, $this->properties ) )
                {
                    throw new jdBasePropertyException( $key, jdBasePropertyException::READ );
                }
                return $this->properties[$key];
        }
    }

    /**
     * Overloaded function to set properties
     * (Default behaviour: Everything not explicitly allowed will be denied)
     *
     * @param mixed $key Property to set
     * @param mixed $val Value to set for property
     * @return void
     */
    public function __set( $key, $val )
    {
        throw new jdBasePropertyException( $key, jdBasePropertyException::WRITE );
    }
}