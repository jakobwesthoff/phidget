<?php
/**
 * jdWidgetFinderAnimationDataStruct
 *
 * @property integer $x The item x offset.
 * @property integer $y The item y offset.
 * @property integer $width The item width.
 * @property integer $height The item height.
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderAnimationDataStruct
{
    /**
     * Magic background properties.
     *
     * @type array<mixed>
     * @var array $properties
     */
    protected $properties = array(
        "x"       =>  0,
        "y"       =>  0,
        "width"   =>  0,
        "height"  =>  0,
    );

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
        switch ( $key )
        {
            case "x":
            case "y":
            case "width":
            case "height":
                $this->properties[$key] = (int) $val;
                break;

            default:
                throw new jdBasePropertyException( $key, jdBasePropertyException::WRITE );
        }
    }
}
