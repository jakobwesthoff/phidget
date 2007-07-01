<?php
/**
 * jdWidgetFinderEffect
 *
 * @property-read array $items All finder bar items.
 * @property-read jdWidgetFinderEffectSizeStruct $sizes Minimum and
 * maximum size for all items.
 * @property-read SimpleXMLElement $configuration The config for the
 * effect implementation.
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
abstract class jdWidgetFinderEffect
{
    private $properties = array();

    public function __construct( array $items, SimpleXMLElement $configuration )
    {
        $this->properties = array(
            "items"          =>  $items,
            "sizes"          =>  null,
            "configuration"  =>  $configuration
        );

        // Calculate size after properties exist.
        $this->properties["sizes"] = $this->calculateSizes();
    }

    public function __get( $key )
    {
        if ( !array_key_exists( $key, $this->properties ) )
        {
            throw new jdBasePropertyException( $key, jdBasePropertyException::READ );
        }
        return $this->properties[$key];
    }

    public function __set( $key, $val )
    {
        throw new jdBasePropertyException( $key, jdBasePropertyException::WRITE );
    }

    public abstract function onMouseMove( GdkEvent $event );

    public abstract function onMouseLeave( GdkEvent $event );

    protected abstract function calculateSizes();
}
