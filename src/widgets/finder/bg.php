<?php
/**
 * jdWidgetFinderBackground
 *
 * @property-read SimpleXMLConfiguration $configuration The background
 * settings from the phidget configuration file.
 * @property jdWidgetFinderEffectSizeStruct $sizes The maximum and
 * minimum width/height for the finder bar.
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
abstract class jdWidgetFinderBackground
{
    /**
     * Factory method for background finder objects.
     *
     * @param SimpleXMLElement $configuration The phidget config for the
     * background.
     * @param jdWidgetFinderEffectSizeStruct $sizes The min/max size
     * structure for the phidget.
     * @return jdWidgetFinderBackground The configured background object.
     */
    public static function createBackground( SimpleXMLElement $configuration,
                                             jdWidgetFinderEffectSizeStruct $sizes )
    {
        // Get background class name
        $className = (string) $configuration["className"];

        // Create a new background
        return new $className( $configuration, $sizes );
    }

    /**
     * Magic background properties.
     *
     * @type array<mixed>
     * @var array $properties
     */
    protected $properties = array();

    /**
     * The ctor takes the background config and the effect size structure
     * as arguments.
     *
     * @param SimpleXMLElement $configuration The background settings.
     * @param jdWidgetFinderEffectSizeStruct $sizes Finder bar size struct.
     */
    protected function __construct( SimpleXMLElement $configuration,
                                    jdWidgetFinderEffectSizeStruct $sizes )
    {
        $this->properties = array(
            "configuration"  =>  $configuration,
            "sizes"          =>  $sizes
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
            case 'sizes':
                $this->properties[$key] = $val;
                break;

            default:
                throw new jdBasePropertyException( $key, jdBasePropertyException::WRITE );
        }
    }

    /**
     * Draws the finder background.
     *
     * @param GdkGC $gc The graphical context.
     * @param GdkEvent $event Current window event.
     */
    public abstract function onExpose( GdkGC $gc, GdkEvent $event );
}
