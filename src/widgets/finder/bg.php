<?php
/**
 * jdWidgetFinderBackground
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderBackground {

    protected $properties = array();

    public function __construct( $filename, jdWidgetFinderEffectSizeStruct $sizes )
    {
        $this->properties = array(
            "pixbuf"  =>  GdkPixbuf::new_from_file( $filename ),
            "sizes"   =>  $sizes,
            "scaled"  =>  round( (int) $sizes->minHeight * 0.9 ),
            "x"       =>  round( ( $sizes->maxWidth - $sizes->minWidth ) * 0.5 ),
            "y"       =>  $sizes->maxHeight - $sizes->minHeight
        );
    }

    public function draw( GdkGC $gc, GdkEvent $event )
    {

        $cmap = $event->window->get_colormap();

        // Calculate x/y offsets and width/height
        $x = ( $event->area->x < $this->x ? $this->x : $event->area->x );
        $w = ( ( $event->area->x + $event->area->width ) < ( $this->x + $this->sizes->minWidth ) ? $event->area->width : ( $this->sizes->minWidth - $x ) );

        // Create a item border
        $gc->set_foreground( $cmap->alloc_color( "#cccccc" ) );
        //$event->window->draw_pixbuf( $gc, $this->pixbuf, 0, 0, $x, $this->y, $w - 1, $this->scaled );
        //$event->window->draw_rectangle( $gc, false, $x, $this->y, $w - 1, $this->scaled );
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
            case 'width':
            case 'height':
            case 'sizes':
                $this->properties[$key] = $val;
                break;

            default:
                throw new jdBasePropertyException( $key, jdBasePropertyException::WRITE );
        }
    }
}
