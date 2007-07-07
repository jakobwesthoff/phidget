<?php
/**
 * jdWidgetFinderTrashItem
 *
 * @property-read GdkPixbuf $pixbufFull The pixbuf object for a full
 * trash.
 * @property-read GdkPixbuf $pixbufEmpty The pixbuf object for an empty
 * trash.
 * @property-read integer $refresh The trash refresh rate.
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
    /**
     * The default reconfiguration timeout with two seconds
     */
    const DEFAULT_REFRESH = 2000;

    /**
     * The parent window that is used to draw this trash item.
     *
     * @type GdkWindow
     * @var GdkWindow $window
     */
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
        $this->properties["pixbufEmpty"] = $this->pixbuf;

        // Generate pixbuf for full trash, we use gnome naming.
        $this->properties["pixbufFull"]  = GdkPixbuf::new_from_file( "{$filename}-full.{$extension}" );

        // Check for a configured refresh rate
        if ( isset( $configuration["refresh"] ) && (int) $configuration["refresh"] > 0 )
        {
            $this->properties["refresh"] = (int) $configuration["refresh"];
        }
        else
        {
            $this->properties["refresh"] = self::DEFAULT_REFRESH;
        }


        // Check current trash state
        $this->checkTrashFiles();

        // Add an initial trash check
        Gtk::timeout_add( $this->refresh, array( $this, "checkTrash" ) );
    }

    /**
     * Main draw method for finder bar items.
     *
     * @param GdkGC $gc The currently used graphical context.
     * @param GdkWindow $window The drawable context for the item.
     */
    public function draw( GdkGC $gc, GdkWindow $window )
    {
        // Keep owner window
        if ( $this->window === null )
        {
            $this->window = $window;
        }

        parent::draw( $gc, $window );
    }

    /**
     * This is a callback method for the Gtk timer. It is registered in
     * the trash ctor and it registers it self on every call. If the
     * last cached trash state has changed and this item was drawn once
     * it will query the parent window for a redraw.
     *
     * @see checkTrashFiles()
     */
    public function checkTrash()
    {
        if ( $this->window !== null && $this->checkTrashFiles() )
        {
             // Ask for redraw
            $this->window->invalidate_rect(
                new GdkRectangle(
                    $this->x,
                    $this->y,
                    $this->size,
                    $this->size
                 ), false
            );
        }

        // Add Gtk timeout for trash check
        Gtk::timeout_add( $this->refresh, array( $this, "checkTrash" ) );
    }

    /**
     * This method checks your local trash folder ~/.Trash and all
     * directories listed under ~/.gnome/gnome-vfs/.trash_entry_cache
     * for any file. If any directory contains a file it sets the item
     * state to full otherwise it will use the empty item.
     *
     * @return boolean If the trash state has changed this method will
     * return <b>true</b> otherwise the return value is <b>false</b>.
     */
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
        $pixbuf = ( $trashFull ? $this->pixbufFull : $this->pixbuffEmpty );

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
