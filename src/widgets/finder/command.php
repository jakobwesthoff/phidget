<?php
/**
 * jdWidgetFinderCommand
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net> 
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderCommand
{
    
    /**
     * The shell command.
     * 
     * @type string
     * @var string $command
     */
    protected $command = "";
    
    /**
     * Constructor takes the shell command as argument.
     *
     * @param string $command
     */
    public function __construct( $command ) 
    {
        // Build gnome terminal command
        // TODO: Check for a better solution
        $this->command = "{$command} > /dev/null 2>&1 &";
        
        // Register gtk timeout
        Gtk::timeout_add( 10, array( $this, "execute" ) );
    }
    
    /**
     * Executes the shell command
     *
     */
    public function execute()
    {
        exec( $this->command );
    }
}
