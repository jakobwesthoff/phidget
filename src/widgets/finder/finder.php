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

        $this->connect( "button-press-event",  array( $this, "onMousePress" ) );
        $this->connect( "motion-notify-event", array( $this, "onMouseMove" ) );
        $this->connect( "leave-notify-event",  array( $this, "onMouseRelease" ) );

        $this->effect = jdWidgetFinderEffect::createEffect(
                            $this->items,
                            $this->configuration->effect
                        );

        // Instantiate the background class
        $this->background = jdWidgetFinderBackground::createBackground(
                                $this->configuration->background,
                                $this->effect->sizes
                            );

        // Get maximum size for the current effect
        $this->size = array(
            $this->effect->sizes->maxWidth,
            $this->effect->sizes->maxHeight
        );

        $anim = new jdWidgetFinderAnimationPulse();

        foreach ( $this->items as $item )
        {
            $this->effect->registerAnimation( $item, $anim );
        }
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
    public function onExpose( GdkGC $gc, GdkEvent $event )
    {
        $this->background->OnExpose( $gc, $event );

        $this->effect->onExpose( $gc, $event );
    }

    /**
     * Event handler for on mouse press events. This method is called for both
     * left and right button clicks.
     *
     * @param jdWidget $source
     * @param GdkEvent $event
     */
    public function onMousePress( jdWidget $source, GdkEvent $event )
    {
        $this->effect->onMousePress( $event );
    }

    /**
     * Event handler for mouse move events. It calculates the different item
     * scalings and item positions.
     *
     * @param jdWidget $source
     * @param GdkEvent $event
     */
    public function onMouseMove( jdWidget $source, GdkEvent $event )
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
    public function onMouseRelease( jdWidget $source, GdkEvent $event )
    {
        $source->window->invalidate_rect(
            $this->effect->onMouseRelease( $event, $this->items ), false
        );
    }
}
