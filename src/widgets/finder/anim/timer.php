<?php
/**
 * Simple gtk timer class for animations.
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderAnimationTimer
{
    /**
     * The context GdkWindow that is required for item redraw.
     *
     * @type GdkWindow
     * @var GdkWindow $window
     */
    private $window;

    /**
     * The context animation object.
     *
     * @type jdWidgetFinderAnimation
     * @var jdWidgetFinderAnimation $anim
     */
    private $anim;

    /**
     * The context item object.
     *
     * @type jdWidgetFinderItem
     * @var jdWidgetFinderItem $item
     */
    private $item;

    /**
     * The ctor takes the gdk window, the animation and the item as
     * arguments.
     *
     * @param GdkWindow $window
     * @param jdWidgetFinderAnimation $anim
     * @param jdWidgetFinderItem $item
     */
    public function __construct( GdkWindow $window,
                                 jdWidgetFinderAnimation $anim,
                                 jdWidgetFinderItem $item )
    {
        $this->window = $window;
        $this->item   = $item;

        $this->anim = clone $anim;

        $this->animateIt();
    }

    /**
     * Internal animation method.
     */
    public function animateIt()
    {
        $data = new jdWidgetFinderAnimationDataStruct();
        $data->x      = $this->item->x;
        $data->y      = $this->item->y;
        $data->width  = $this->item->width;
        $data->height = $this->item->height;

        $ret = $this->anim->animate( $data );

        $this->item->x      = $data->x;
        $this->item->y      = $data->y;
        $this->item->width  = $data->width;
        $this->item->height = $data->height;

        $this->window->invalidate_rect(
            new GdkRectangle( $this->item->x, $this->item->y, $this->item->width, $this->item->height ), false
        );
        if ( $ret === true )
        {
            Gtk::timeout_add( $this->anim->register(), array( $this, "process" ) );
        }

    }
}

