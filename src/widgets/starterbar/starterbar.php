<?php
/**
 *
 * @author Manuel Pichler <pichler@i-world.de>
 * @version 0.1
 */
class jdWidgetStarterbar extends jdWidget
{

    /**
     * Temporary all icons for the starterbar. This should be 
     * configurable.
     *
     * @type array<string>
     * @var array $_icons
     */
    private $_icons = array(
        "pixmaps/terminal.png", 
        "pixmaps/web-browser.png", 
        "pixmaps/help.png" );

    /**
     * <tt> Array</tt> with all starterbar items.
     *
     * @type array<jdWidgetStarterbarIcon>
     * @var array $_items
     */
    private $_items = array();

    /**
     *
     */
    private $_width = 0;

    public function __construct( $configuration ) {
        parent::__construct( $configuration );

        $offsetX = 0;
        foreach ( $this->_icons as $icon ) {
            $this->_items[] = new jdWidgetStarterbarIcon( $icon, $offsetX, 64 );
            
            $offsetX += 80;
        } 

        $this->add_events( Gdk::BUTTON_PRESS_MASK |
                           Gdk::POINTER_MOTION_MASK );
        $this->connect( "button-press-event", array( $this, "OnMousePress" ) );
        $this->connect( "motion-notify-event", array( $this, "OnMouseMove" ) );

    }

    protected function getSize()
    {
        return array( 50 + sizeof( $this->_icons ) * 80, 150 );
    }

    public function OnExpose( $gc, $window )
    {
        $cmap = $window->get_colormap();
        $size = $this->getSize();

        $gc->set_foreground( $cmap->alloc_color( "#000000" ) );
        $window->draw_rectangle( $gc, false, 0, 0, $size[0] - 1, 149 );

        foreach ( $this->_items as $item ) {
            $item->OnExpose( $gc, $window );
        }
    }

    public function OnMousePress(jdWidget $source, GdkEvent $event) {
        print "CLICK ME\n";
    }

    private $_random = 0;

    private $_eventX = 0;
    private $_eventY = 0;

    public function OnMouseMove(jdWidget $source, GdkEvent $event) {
        $scale = 0.0;
        $index = 0;

        if ( $event->x === $this->_eventX && $event->y === $this->_eventY ) {
            return;
        }
        $this->_eventX = $event->x;
        $this->_eventY = $event->y;

//        if ( $this->_random++ % 30 !== 0 ) { return; }
        
        $index = -1;
        $scale = 0.0;

        foreach ( $this->_items as $i => $item ) {
            if ( ( $s = $item->scaleForEvent( $event ) ) > 0.2 ) {
                // Ask for redraw
                $source->window->invalidate_rect(
		    new GdkRectangle(
                        $item->OffsetX, 0, $item->Width, $item->Height), false );

                $scale = $s;
                $index = $i;

                break;
            }
        }
    }
}

