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

    /**
     * Main draw method for finder bar items.
     *
     * @param GdkGC $gc The currently used graphical context.
     * @param GdkWindow $window The drawable context for the item.
     */
    public function draw( GdkGC $gc, GdkWindow $window )
    {
        // Create a scaled pixbuf
        $pixbuf = $this->pixbuf->scale_simple( $this->width, $this->height, Gdk::INTERP_HYPER );

        // Draw new pixbuf to drawable context
        $window->draw_pixbuf( $gc, $pixbuf, 0, 0, $this->x, $this->y );

        // Free pixbuf resource
        unset( $pixbuf );
    }

    /**
     * This item recieved a left clicked.
     *
     * @param GdkEvent $event
     */
    public function onMouseClick( GdkEvent $event )
    {
        print "CLICK(" . ( (string) $this->configuration->command ) . ")\n";return;
        // Create new jump animation
        // TODO: Make this configurable?
        $animation = new jdWidgetFinderAnimationJump( $this, $event->window );

        // Start animation
        $animation->animate();

        // Build finder command
        new jdWidgetFinderCommand( (string) $this->configuration->command );
    }
}
