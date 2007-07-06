<?php
/**
 * jdWidgetFinderItem
 *
 * @property-read SimpleXMLElement $configuration Settings from the widget
 * config file.
 * @property integer $width The current item width.
 * @property integer $height The current item height.
 * @property integer $x The current item x offset.
 * @property integer $y The current item y offset.
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
            "width"          =>  $size,
            "height"         =>  $size,
            "x"              =>  0,
            "y"              =>  0
        );
    }

    /**
     * Template init method for items. You can use this method for init
     * tasks that require a complete environment setup.
     */
    public function init()
    {
        // Nothing here
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
            case "width":
            case "height":
            case "locked":
                $this->properties[$key] = $val;
                break;

            default:
                throw new jdBasePropertyException( $key, jdBasePropertyException::WRITE );
        }
    }

    /**
     * Empty template method for mouse clicks.
     *
     * @param GdkEvent $event
     */
    public function onMouseClick( GdkEvent $event )
    {

    }

    public abstract function draw( GdkGC $gc, GdkWindow $window );
}
