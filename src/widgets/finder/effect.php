<?php
/**
 * jdWidgetFinderEffect
 *
 * @property-read array $items All finder bar items.
 * @property-read jdWidgetFinderEffectSizeStruct $sizes Minimum and
 * maximum size for all items.
 * @property-read SimpleXMLElement $configuration The config for the
 * effect implementation.
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
abstract class jdWidgetFinderEffect
{
    /**
     * Factory method for the finder bar effect.
     *
     * @param array $items All items displayed in the finder bar.
     * @param SimpleXMLElement $configuration The effect settings from the
     * phidget configuration file.
     * @return jdWidgetFinderEffect The concrete effect implementation.
     *
     * @throws jdBaseClassNotFoundException If the configured effect class
     * doesn't exist.
     */
    public static function createEffect( array $items, SimpleXMLElement $configuration )
    {
        // Get effect class from config
        $className = (string) $configuration["className"];

        // Check for class existence
        if ( class_exists( $className, true ) === false )
        {
            throw new jdBaseClassNotFoundException( $className );
        }

        // Create a new effect instance.
        return new $className( $items, $configuration );
    }

    /**
     * Magic properties for the effect implementation.
     *
     * @type array<mixed>
     * @var array $properties
     */
    private $properties = array();

    /**
     * Two dimensional array with registered animations for an item. The
     * internal item index is used to map animations and items.
     *
     * <code>
     *   array(
     *       0  =>  array(
     *           0  =>  jdWidgetFinderAnimationPulse,
     *           1  =>  jdWidgetFinderAnimationJump
     *       ),
     *       2  =>  array(
     *           0  =>  jdWidgetFinderAnimationJump
     *       )
     *   )
     * </code>
     *
     * @type array<array>
     * @var array $annimations
     */
    private $animations = array();

    /**
     * The ctor takes an array with finder items and the settings from the
     * phidget configuration file as arguments.
     *
     * @param array $items Registered finder items.
     * @param SimpleXMLElement $configuration The effect configuration.
     */
    protected function __construct( array $items, SimpleXMLElement $configuration )
    {
        $this->properties = array(
            "items"          =>  $items,
            "sizes"          =>  null,
            "configuration"  =>  $configuration
        );

        // Calculate size after properties exist.
        $this->properties["sizes"] = $this->calculateSizes();
    }

    /**
     * Register an arbitrary animation object for a specified item. This
     * function should not be overridden in any deriving class, therefore
     * it is declared to be final.
     *
     * @param jdWidgetFinderItem $item The item to animate.
     * @param jdWidgetFinderAnimation The animation instance.
     */
    public final function registerAnimation( jdWidgetFinderItem $item,
                                             jdWidgetFinderAnimation $anim )
    {
        // Get item index
        if ( ( $idx = array_search( $item, $this->items ) ) === false )
        {
            return;
        }
        // Check for an existing animation container
        if ( !isset( $this->animations[$idx] ) )
        {
            $this->animations[$idx] = array();
        }
        // Store animation object
        $this->animations[$idx][] = $anim;
    }

    /**
     * Returns an array of animation objects registered for the specified item
     * object. If no animation is registered for the specified item an empty
     * array is returned.
     * This function should not be overridden in any deriving class, therefore
     * it is declared to be final.
     *
     * @param jdWidgetFinderItem $item The item to animate.
     */
    public final function getRegisteredAnimations( jdWidgetFinderItem $item )
    {
        // Get item index
        if ( ( $idx = array_search( $item, $this->items ) ) === false )
        {
            return;
        }
        // Check for an existing animation container
        if ( isset( $this->animations[$idx] ) )
        {
            return $this->animations[$idx];
        }
        return array();
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
        if ( !array_key_exists( $key, $this->properties ) )
        {
            throw new jdBasePropertyException( $key, jdBasePropertyException::READ );
        }
        return $this->properties[$key];
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
        throw new jdBasePropertyException( $key, jdBasePropertyException::WRITE );
    }

    /**
     * The onExpose method is called every time the widget needs to be redrawn.
     * It is advised to redraw only the area stored in the given GdkEvent for
     * performance reasons.
     *
     * @param GdkGC $gc The graphical context.
     * @param GdkEvent $event The recieved event with the effected redraw
     * area.
     */
    public abstract function onExpose( GdkGC $gc, GdkEvent $event );

    /**
     * The onMouseMove method is called every time the mouse pointer moves
     * over the widget and a redraw is needed. It is advised to redraw only
     * the area stored in the given GdkEvent for performance reasons.
     *
     * @param GdkEvent $event The recieved event with the effected redraw
     * area.
     */
    public abstract function onMouseMove( GdkEvent $event );

    /**
     * The onMousePress method is called every time a mouse button was clicked
     * and a redraw is needed. It is advised to redraw only the area stored in
     * the given GdkEvent for performance reasons.
     *
     * @param GdkEvent $event The recieved event with the effected redraw
     * area.
     */
    public abstract function onMousePress( GdkEvent $event );

    /**
     * The onMouseMove method is called every time the mouse pointer leaves
     * the widget and a redraw is needed. It is advised to redraw only the
     * area stored in the given GdkEvent for performance reasons.
     *
     * @param GdkEvent $event The recieved event with the effected redraw
     * area.
     */
    public abstract function onMouseRelease( GdkEvent $event );

    /**
     * Internal size calculation method. This is needed because every effect
     * implementation has it's own minimum and maximum working area.
     *
     * @return jdWidgetFinderEffectSizeStruct
     */
    protected abstract function calculateSizes();
}

class jdWidgetFinderEffectTimer
{
    private $window;
    private $anim;
    private $item;

    public function __construct( $window, $anim, $item )
    {
        $this->window = $window;
        $this->item   = $item;

        $this->anim = clone $anim;

        $this->process();
    }

    public function process()
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
