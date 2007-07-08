<?php
/**
 * jdWidgetFinderEffectSizeStruct
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderEffectSizeStruct
{
    /**
     * Magic background properties.
     *
     * @type array<mixed>
     * @var array $properties
     */
    protected $properties = array();

    public function __construct( $minWidth, $minHeight, $maxWitdh, $maxHeight )
    {
        $this->properties = array(
            "minWidth"   =>  $minWidth,
            "minHeight"  =>  $minHeight,
            "maxWidth"   =>  $maxWitdh,
            "maxHeight"  =>  $maxHeight,
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
        if ( !array_key_exists( $key, $this->properties ) )
        {
            throw new jdBasePropertyException( $key, jdBasePropertyException::READ );
        }
        return $this->properties[$key];
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
