<?php
/**
 * jdWidgetFinderCommand
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff. All rights reserved.
 * @author Manuel Pichler <mp@manuel-pichler.de>
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
        $this->command = "{$command} &";
        
        // Register gtk timeout
        Gtk::timeout_add( 100, array( $this, "execute" ) );
    }
    
    /**
     * Executes the shell command
     *
     */
    public function execute()
    {
        system( $this->command );
    }
}