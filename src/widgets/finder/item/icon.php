<?php
/**
 * jdWidgetFinderIconItem
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderIconItem extends jdWidgetFinderItem
{
    /**
     * Constructor takes the configuration, the x/y offset and the item size as
     * argument.
     *
     * @param SimpleXMLElement $configuration
     * @param integer $size
     */
    public function __construct( SimpleXMLElement $configuration, $size )
    {
        parent::__construct(  $configuration, $size );

        // Set up icon item properties
        $this->properties["pixbuf"]  = GdkPixbuf::new_from_file( (string) $configuration->icon );
    }

    public function draw( GdkGC $gc, GdkWindow $window )
    {
        $pixbuf = $this->pixbuf->scale_simple( $this->size, $this->size, Gdk::INTERP_HYPER );

        // The icon position is defined by its center point, but gdk needs the top left corner.
        // Calc the new point and draw.
        $window->draw_pixbuf( $gc, $pixbuf, 0, 0, $this->x, $this->y );
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
