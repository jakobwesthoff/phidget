<?php
/**
 * jdWidgetFinderTrashItem
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mp@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderTrashItem extends jdWidgetFinderItem
{

    protected $window = null;

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
        // Keep owner window
        if ( $this->window === null )
        {
            $this->window = $window;
        }
         
        // Scale this pixbuf
        $pixbuf = $this->pixbuf->scale_simple( $this->size, $this->size, Gdk::INTERP_HYPER );
         
        // Draw current pixbuf
        $window->draw_pixbuf( $gc, $pixbuf, 0, 0, $this->x - round( $this->size / 2.0 ), $this->y - round( $this->size / 2.0 ) );
         
        // Free resource
        unset( $pixbuf );
    }
}