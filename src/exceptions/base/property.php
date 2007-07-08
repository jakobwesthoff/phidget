<?php
/**
 * This type of exception is thrown if a requested properties is not readable,
 * writeable or doesn't exist. These error types should be detectable during
 * compile time, so it is a logic exception.
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdBasePropertyException extends LogicException
{
    /**
     * Use this constant for invalid read access.
     */
    const READ = 1;

    /**
     * Use this constant for invalid write access.
     */
    const WRITE = 2;

    /**
     * The ctor takes the property key/name and the invalid access type
     * read/write as arguments.
     *
     * @param string $key The property name.
     * @param string $type The property access type.
     */
    public function __construct( $key, $type )
    {
        parent::__construct( 'Invalid property access. The property "' . $key . '" is not '. ( $type === self::READ ? 'readable' : 'writable' ) . '.'  );
    }
}
