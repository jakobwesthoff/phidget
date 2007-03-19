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
    
    public function __construct( $filename, $width, $height, $size )
    {
        $this->properties = array(
            "pixbuf" => GdkPixbuf::new_from_file( $filename ),
            "width"  => $width,
            "height" => $height,
            "size"   => $size);
    }
    
    public function draw( GdkGC $gc, GdkWindow $window )
    {
        
        $cmap = $window->get_colormap();
        
        // Calculate x/y offsets and width/height
        $scaled  = round( (int) $this->size * 0.9 );
        $offsetY = $this->height -  $this->size;
        $width   = $this->width - ( 2 * $scaled );

        // Create a item border
        $gc->set_foreground( $cmap->alloc_color( "#cccccc" ) );
        $window->draw_pixbuf( $gc, $this->pixbuf, 0, 0, $scaled, $offsetY, $width - 1, $scaled );
        $window->draw_rectangle( $gc, false, $scaled, $offsetY, $width - 1, $scaled );
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
            case 'size':
                $this->properties[$key] = $val;
                break;

            default:
                throw new jdBasePropertyException( $key, jdBasePropertyException::WRITE );
        }
    }
}
