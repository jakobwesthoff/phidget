<?php
/**
 * jdWidgetFinderItem
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
abstract class jdWidgetFinderItem
{

    /**
     * Factory method for the different finder items.
     *
     * @param SimpleXMLElement $widgetconf
     * @param SimpleXMLElement $iconconf
     */
    public static function createItem( SimpleXMLElement $widgetconf,
                                       SimpleXMLElement $iconconf )
    {

        // Split at all separator chars
        $type = preg_split( "#[-_\.]#", strtolower( (string) $iconconf->type ) );

        // Strip all empty elements
        $type = array_filter( $type );

        // If size is one, use default namespace
        if ( count( $type ) <= 1 )
        {
            array_unshift( $type, "widget" );
        }

        // No type given, use default "icon"
        if ( count( $type ) === 1 )
        {
            array_push( $type, "icon" );
        }

        // Camel case all words
        $type = array_map( "ucfirst", $type );

        // Generate class name for item
        $class = "jd{$type[0]}Finder{$type[1]}Item";

        // Load item class and create instance
        $refClass = new ReflectionClass( $class );

        return $refClass->newInstanceArgs(
            array( $iconconf, (int) $widgetconf->size )
        );
    }

    /**
     * All properties for the item implementation.
     *
     * @type array<mixed>
     * @var array $properties
     */
    protected $properties = array();

    /**
     * Constructor takes the configuration and the item size as argument.
     *
     * @param SimpleXMLElement $configuration
     * @param integer $size
     */
    public function __construct( SimpleXMLElement $configuration, $size )
    {
        $this->properties = array(
            "configuration"  =>  $configuration,
            "locked"         =>  false,
            "size"           =>  $size,
            "x"              =>  0,
            "y"              =>  0
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
