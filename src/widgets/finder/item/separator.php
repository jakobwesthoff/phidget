<?php
/**
 * jdWidgetFinderSeparator
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderSeparatorItem extends jdWidgetFinderItem
{
    /**
     * Separator item width.
     */
    const SEPARATOR_WIDTH = 2;

    public function __construct( SimpleXMLElement $configuration, $size )
    {
        parent::__construct( $configuration, self::SEPARATOR_WIDTH );

        // Keep given size as separator height
        $this->properties["height"] = $size;
    }

    /**
     * Main drawing method
     */
    public function draw( GdkGC $gc, GdkWindow $window )
    {

        $cmap = $window->get_colormap();

        // TODO: Make colors configurable?
        // Allocate colors for separator
        $colorlight = $cmap->alloc_color( "#dddddd" );
        $colordark  = $cmap->alloc_color( "#999999" );

        // Calculate x/y offset
        $x0 = $this->x - 1;
        $x1 = $this->x;
        $y0 = $this->y - ( $this->height / 2 ) + 5;
        $y1 = $this->y + ( $this->height / 2 ) - 10;

        $gc->set_foreground( $colorlight );
        $window->draw_line( $gc, $x0, $y0, $x0, $y1 );

        $gc->set_foreground( $colordark );
        $window->draw_line( $gc, $x1, $y0, $x1, $y1 );
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
            // Overload default behaviour, do not throw an exception for invalid
            // write access. I think most item implementations ( trash, clock )
            // will scale and move their y offset.
            // TODO: Ask jakob what he thinks!?
            case "size":
            case "y":
                break;

            default:
                parent::__set( $key, $val );
                break;
        }
    }
}
