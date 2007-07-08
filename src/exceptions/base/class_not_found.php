<?php
/**
 * This type of exception is thrown if a configured class doesn't exist. This
 * can only happen during the phidget runtime phase if a wrong typed or unknown
 * className is passed to a factory.
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdBaseClassNotFoundException extends RuntimeException
{
    /**
     * The ctor takes the class name as argument.
     *
     * @param string $className
     */
    public function __construct( $className )
    {
        parent::__construct( "Class {className} cannot be found." );
    }
}
