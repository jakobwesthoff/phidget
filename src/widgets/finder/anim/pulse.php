<?php
/**
 * jdWidgetFinderAnimationPulse
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderAnimationPulse implements jdWidgetFinderAnimation
{
    /**
     * The register function is called if this animation object is registered
     * for any item. It must return the delay after which a redraw and therefore
     * a call to {@link animate()} should be issued.
     *
     * @return integer The redraw delay in milliseconds.
     */
    public function register()
    {
        return 100;
    }

    /**
     * This function is called every time the animation needs to be applied.
     * The jdWidgetFinderAnimationDataStruct may be manipulated in any way
     * you can think of.
     * The return value of this function must be either true or false. It
     * indicates whether the animation is completed or not.
     *
     * @param jdWidgetFinderAnimationDataStruct $date The item data struct.
     * @return boolean true while this animation needs another redraw otherwise
     * false.
     */
    public function animate( jdWidgetFinderAnimationDataStruct $data )
    {
        return false;
    }
}
