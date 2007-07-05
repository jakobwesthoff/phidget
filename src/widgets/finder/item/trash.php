<?php
/**
 * jdWidgetFinderTrashItem
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderTrashItem extends jdWidgetFinderIconItem
{

    protected $window = null;

    /**
     * Constructor takes the configuration and the item size as argument.
     *
     * @param SimpleXMLElement $configuration
     * @param integer $size
     */
    public function __construct( SimpleXMLElement $configuration, $size )
    {
        parent::__construct(  $configuration, $size );

        // TODO: What happens with dotted file names?
        // Extract filename and extension
        list( $filename, $extension ) = explode( ".", (string) $configuration->icon );

        // Set current pixbuf as empty, we know icon will set this property
        $this->properties["pbempty"] = $this->pixbuf;

        // Generate pixbuf for full trash, we use gnome naming.
        $this->properties["pbfull"]  = GdkPixbuf::new_from_file( "{$filename}-full.{$extension}" );

        // Check current trash state
        $this->checkTrashFiles();
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
        switch( $key )
        {
            case "icon":
            case "state":
            case "pixbuf":
                $this->properties[$key] = $val;
                break;

            default:
                parent::__set( $key, $val );
                break;
        }
    }

    public function draw( GdkGC $gc, GdkWindow $window )
    {
        parent::draw( $gc, $window );

        // Keep owner window
        if ( $this->window === null )
        {
            $this->window = $window;
        }

        // Add Gtk timeout for trash check
        Gtk::timeout_add( 2000, array( $this, "checkTrash" ) );
    }

    /**
     * This item recieved a left clicked.
     *
     * @param GdkWindow $window
     */
    public function doLeftClick( GdkWindow $window )
    {
        // Nothing todo:
        // TODO: Clear on double click???
    }

    public function checkTrash()
    {
         if ( $this->checkTrashFiles() )
         {
             print "CHANGE\n";
             // Ask for redraw
            $this->window->invalidate_rect(
                new GdkRectangle(
                    $this->x - ( $this->size / 2 ),
                    $this->y - ( $this->size / 2 ),
                    $this->size,
                    $this->size
                 ), false
            );
         }
    }

    protected function checkTrashFiles()
    {

        // Get user home directory
        $home = getenv( "HOME" );

        // Home directory trash
        $trashHome = "{$home}/.Trash";

        // Is the gnome trash full
        $trashFull = false;

        // Check directory exists
        if ( file_exists( $trashHome ) )
        {

            // Create a new directory iterator and check for files.
            $it = new DirectoryIterator( $trashHome );
            while ( $it->valid() )
            {
                // If a file exists, return true
                if ( !$it->isDot() )
                {
                    $trashFull = true;
                    break;
                }
                // Move to next entry
                $it->next();
            }
        }

        // The trash file name
        $trashFile = "{$home}/.gnome/gnome-vfs/.trash_entry_cache";

        if ( !$trashFull && file_exists( $trashFile ) )
        {

            // Open trash file and iterate
            foreach ( file( $trashFile ) as $line )
            {
                // Extract trash directory
                list( , $path ) = explode( " ", $line );

                $path = trim( $path );

                // If the path is "-" skip here
                if ( $path === "-" )
                {
                    continue;
                }


                // Check directory exists
                if ( !file_exists( $path ) )
                {
                    continue;
                }

                // Create iterator and check for files
                $it = new DirectoryIterator( $path );
                while ( $it->valid() )
                {
                    // If it is a file, return true.
                    if ( !$it->isDot() )
                    {
                        $trashFull = true;
                        break;
                    }
                    // Move to next entry
                    $it->next();
                }
            }
        }

        // Extract current state icon
        $pixbuf = ( $trashFull ? $this->pbfull : $this->pbempty );

        // Check for difference
        if ( $pixbuf !== $this->pixbuf )
        {
            // Set current state pixbuf as default
            $this->pixbuf = $pixbuf;

            return true;
        }
        return false;

    }
}
