<?php
/**
 * jdWidgetFinderAnimationJump
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net> 
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderAnimationJump implements jdWidgetFinderAnimation 
{

    protected $item = null;
    protected $window = null;

    protected $properties = array();

    public function __construct( jdWidgetFinderItem $item, GdkWindow $window )
    {
        $this->item   = $item;
        $this->window = $window;

        // If the given item is locked, skip here
        if ( $this->item->locked )
        {
            return;
        }
        // Set current icon locked
        $this->item->locked = true;

        // Get current window size
        $size = $this->window->get_size();

        $this->properties = array(
            "count"   =>  0,
            "step"    =>  3,
            "size"    =>  $this->item->size,
            "jump"    =>  1 + abs( floor( ( $size[1] - $this->item->size ) / 3 ) ),
            "x"       =>  $this->item->x,
            "y"       =>  $this->item->y
        );
    }

    public function animate()
    {
        if ( $this->count % $this->jump === 0 )
        {
            $this->step *= -1;
        }

        if ( $this->count < ( 4 * $this->jump ) ) {

            $this->y += $this->step;

            $this->item->x    = $this->x;
            $this->item->y    = $this->y;
            $this->item->size = $this->size;

            Gtk::timeout_add( 2, array( $this, "animate" ) );
        } else {
            // Set current icon unlocked
            $this->item->locked = false;
        }

        $size = $this->window->get_size();

        $this->window->invalidate_rect(
            new GdkRectangle( 0, 0, $size[0], $size[1] ), false
        );

        $this->count++;
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
            case "y":
            case "step":
            case "count":
                $this->properties[$key] = $val;
                break;

            default:
                throw new jdBasePropertyException( $key, jdBasePropertyException::WRITE );
        }
    }
}
