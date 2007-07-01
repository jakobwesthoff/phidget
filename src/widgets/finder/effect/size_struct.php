<?php
class jdWidgetFinderEffectSizeStruct
{
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
}
