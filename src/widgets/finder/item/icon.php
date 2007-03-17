<?php
/**
 * jdWidgetFinderIconItem
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff. All rights reserved.
 * @author Manuel Pichler <mp@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderIconItem extends jdWidgetFinderItem
{
    
    /**
     * Constructor takes the configuration, the x/y offset and the item size as
     * argument.
     *
     * @param SimpleXMLElement $configuration
     * @param integer $x
     * @param integer $y
     * @param integer $size
     */
    public function __construct( SimpleXMLElement $configuration, $x, $y, $size )
    {
        parent::__construct(  $configuration, $x, $y, $size );

        // Set up icon item properties
        $this->properties["pixbuf"]  = GdkPixbuf::new_from_file( (string) $configuration->icon );
    }

    public function draw( GdkGC $gc, GdkWindow $window )
    {
        $pixbuf = $this->pixbuf->scale_simple( $this->size, $this->size, Gdk::INTERP_HYPER );
        // @todo: there is something completely wrong with this center to draw point calculation
        //        But I am too tired to fix this now :)
        $window->draw_pixbuf( $gc, $pixbuf, 0, 0, $this->x - round( $this->size / 2.0 ), $this->y - round( $this->size / 2.0 ) );
        unset( $pixbuf );
    }

    /**
     * This item recieved a left clicked.
     *
     * @param GdkWindow $window
     */
    public function doLeftClick( GdkWindow $window )
    {
        // Create new jump animation
	    // TODO: Make this configurable?
        $animation = new jdWidgetFinderAnimationJump( $this, $window );
        
        // Start animation
        $animation->animate();
        
        // Build finder command
	    new jdWidgetFinderCommand( (string) $this->configuration->command );
    }
}