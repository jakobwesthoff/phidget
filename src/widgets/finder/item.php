<?php
/**
 * jdWidgetFinderItem
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net> 
 * @author Manuel Pichler <mp@manuel-pichler.de>
 * @license GPL
 */
abstract class jdWidgetFinderItem 
{
    
    /**
     * Factory method for the different finder items. 
     *
     * @param SimpleXMLElement $widgetconf
     * @param SimpleXMLElement $iconconf
     * @param integer $x
     * @param integer $y
     */
    public static function createItem( SimpleXMLElement $widgetconf, 
                                       SimpleXMLElement $iconconf,
                                       $x, 
                                       $y )
    {

        // Switch case is bad for a factory
	    // TODO: Refactor this into a nicer structure.
	    switch ( (string) $iconconf->type )
	    {
	        case "separator":
	            return new jdWidgetFinderSeparator(
	                                $iconconf, $x, $y, (int) $widgetconf->size );
	                                
	        case "clock":
	            return new jdWidgetFinderClockItem(
	                                $iconconf, $x, $y, (int) $widgetconf->size );
	             
	        case "trash":
	            return new jdWidgetFinderTrashItem(
	                                $iconconf, $x, $y, (int) $widgetconf->size );
	                                
	        case "icon":
	        default: 
	            return new jdWidgetFinderIconItem(
	                                $iconconf, $x, $y, (int) $widgetconf->size );
	    }
    }
    
    /**
     * All properties for the item implementation.
     *
     * @type array<mixed>
     * @var array $properties
     */
    protected $properties = array();
    
    /**
     * Constructor takes the configuration, the x/y offset and the item size as 
     * argument.
     * 
     * @param SimpleXMLElement $configuration
     * @param integer $x
     * @param integer $y
     * @param integer $size
     */
    public function __construct( SimpleXMLElement $configuration, $x, $y, $size )
    {
        $this->properties = array(
            "configuration"  =>  $configuration,
            "locked"         =>  false,
            "size"           =>  $size,
            "x"              =>  $x,
            "y"              =>  $y
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
        switch( $key )
        {
            case "x":
            case "y":
            case "size":
            case "locked":
                $this->properties[$key] = $val;
                break;

            default:
                throw new jdBasePropertyException( $key, jdBasePropertyException::WRITE );
        }
    }
    
    /**
     * Empty template method for left mouse click.
     *
     * @param GdkWindow $window
     */
    public function doLeftClick( GdkWindow $window ) 
    {
        
    }

    /**
     * Empty template method for right mouse click.
     *
     * @param GdkWindow $window
     */
    public function doRightClick( GdkWindow $window )
    {

    }

    public abstract function draw( GdkGC $gc, GdkWindow $window );
}
