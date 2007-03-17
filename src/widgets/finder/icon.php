<?php

class jdWidgetFinderIcon {

    protected $properties;

    public function __construct( $filename )
    {
        $this->properties = array( 
            'pixbuf'        =>  GdkPixbuf::new_from_file( $filename ),
            'size'          =>  0,
            'x'             =>  0,
            'y'             =>  0,
        );
    }

    public function draw( $gc, $window )
    {
        $pixbuf = $this->pixbuf->scale_simple( $this->size, $this->size, Gdk::INTERP_HYPER );                
	    $window->draw_pixbuf( $gc, $pixbuf, 0, 0, $this->x - round( $this->size / 2.0 ), $this->y - round( $this->size / 2.0 ) );
        unset( $pixbuf );
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
            case 'x':
            case 'y':
            case 'size':
                $this->properties[$key] = $val;
            break;

            default:                
                throw new jdBasePropertyException( $key, jdBasePropertyException::WRITE );
        }
    }
}

?>
