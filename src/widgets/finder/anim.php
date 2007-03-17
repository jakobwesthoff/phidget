<?php
/**
 * jdWidgetFinderAnimation
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net> 
 * @author Manuel Pichler <mp@manuel-pichler.de>
 * @license GPL
 */
interface jdWidgetFinderAnimation 
{
    function __construct( jdWidgetFinderItem $item, GdkWindow $window );
    
    function animate();    
}
