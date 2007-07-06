<?php
/**
 * jdWidgetFinder
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinder extends jdWidget
{

    /**
     * Use this property to render an additional debug lines etc.
     *
     * @type boolean
     * @var boolean $debug
     */
    protected $debug;

    /**
     * The finder bar background.
     *
     * @type jdWidgetFinderBackground
     * @var jdWidgetFinderBackground $background
     */
    protected $background;

    /**
     * <tt>Array</tt> of <tt>jdWidgetFinderItem</tt> instances.
     *
     * @type array<jdWidgetFinderItem>
     * @var array $items
     */
    protected $items;

    /**
     * <tt>Array</tt> with the calculated widget size.
     *
     * <code>
     *   array(
     *       0  =>  (widget width),
     *       1  =>  (widget height)
     *   );
     * </code>
     *
     * @type array<integer>
     * @var array $size
     */
    protected $size;

    /**
     * Mouse x offset from the last recieved event.
     *
     * @type integer
     * @var integer $lastMouseX
     */
    protected $lastMouseX;

    /**
     * Mouse y offset from the last recieved event.
     *
     * @type integer
     * @var integer $lastMouseY
     */
    protected $lastMouseY;

    protected $effect = null;

    /**
     * Main init method for a widget, this method is called from the
     * <tt>jdWidget</tt> constructor.
     *
     */
    protected function init()
    {
        // Initialize class members
        $this->items = array();
        $this->lastMouseX = 0;
        $this->lastMouseY = 0;

        // Check for debug setting
        $this->debug = (bool) ( isset( $this->configuration["debug"] ) && (string) $this->configuration["debug"] === "true" );

        foreach ( $this->configuration->items->item as $itemconfig )
        {
            // Ask factory for new item instance
            $item = jdWidgetFinderItem::createItem(
                                            $this->configuration,
                                            $itemconfig
                                        );

            $this->items[] = $item;
        }

        // Connect to the needed signals
        $this->add_events(
             Gdk::BUTTON_PRESS_MASK
           | Gdk::POINTER_MOTION_MASK
           | Gdk::LEAVE_NOTIFY_MASK
        );

        $this->connect( "button-press-event",  array( $this, "OnMousePress" ) );
        $this->connect( "motion-notify-event", array( $this, "OnMouseMove" ) );
        $this->connect( "leave-notify-event",  array( $this, "OnMouseLeave" ) );

        $this->effect = new jdWidgetFinderEffectScale(
                                $this->items,
                                $this->configuration->effect
                            );

        // Instantiate the background class
        $this->background = new jdWidgetFinderBackground(
                                    (string) $this->configuration->background,
                                    $this->effect->sizes
                                );

        // Get maximum size for the current effect
        $this->size = array(
            $this->effect->sizes->maxWidth,
            $this->effect->sizes->maxHeight
        );
    }

    /**
     * Returns the size of the widget implementation.
     *
     * <code>
     *   array(
     *       0  =>  (widget width),
     *       1  =>  (widget height)
     *   );
     * </code>
     *
     * @return array
     */
    protected function getSize()
    {
        return $this->size;
    }

    /**
     * Main drawing method for a widget.
     *
     * @param GdkGC $gc
     * @param GdkWindow $window
     */
    public function OnExpose( GdkGC $gc, GdkEvent $event )
    {
        $cmap = $event->window->get_colormap();

        // DEBUG: Draw border around the widget
        if ( $this->debug === true )
        {
            $gc->set_foreground( $cmap->alloc_color( "#000000" ) );
            $event->window->draw_rectangle( $gc, false, 0, 0, $this->size[0] - 1, $this->size[1] - 1 );
        }

        // Draw the background on the surface
        $this->background->draw( $gc, $event );

        $area = $event->area;

        // Draw every icon to the widget surface
        foreach( $this->items as $item )
        {
            if ( $item->x < $area->x || $item->x > ( $area->x + $area->width ) )
            {
                continue;
            }

            $item->draw( $gc, $event->window );
        }
    }

    /**
     * Event handler for on mouse press events. This method is called for both
     * left and right button clicks.
     *
     * @param jdWidget $source
     * @param GdkEvent $event
     */
    public function OnMousePress( jdWidget $source, GdkEvent $event )
    {
        // Find the matching item
        foreach ( $this->items as $item )
        {
            // Calculate item x min/max range
            $maxX = $item->x + $item->size;

            if ( $event->x <= $maxX && $event->x >= $item->x )
            {
                $item->onMouseClick( $event );
                break;
            }
        }
    }

    /**
     * Event handler for mouse move events. It calculates the different item
     * scalings and item positions.
     *
     * @param jdWidget $source
     * @param GdkEvent $event
     */
    public function OnMouseMove( jdWidget $source, GdkEvent $event )
    {
        if ( $this->lastMouseX === $event->x
           && $this->lastMouseY === $event->y )
        {
            // No change needed, because the mouse posistion did not change
            return;
        }

        $this->lastMouseX = $event->x;
        $this->lastMouseY = $event->y;

        $source->window->invalidate_rect(
            $this->effect->onMouseMove( $event, $this->items ), false
        );
    }

    /**
     * Event handler for mouse leave events. This method delegates to the
     * <tt>OnMouseMove()</tt> method, it ensures zero scaling for quick mice
     * movers ;)
     *
     * @param jdWidget $source
     * @param GdkEvent $event
     */
    public function OnMouseLeave( jdWidget $source, GdkEvent $event )
    {
        $source->window->invalidate_rect(
            $this->effect->onMouseLeave( $event, $this->items ), false
        );
    }
}
