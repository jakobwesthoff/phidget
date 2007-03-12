<?php


class jdWidgetStarterbar extends jdWidget
{

    private $_icons = array(
        "pixmaps/terminal.png", 
        "pixmaps/web-browser.png", 
        "pixmaps/help.png" );

    private $_items = array();

    private $_width = 0;

    public function __construct( $configuration ) {
        parent::__construct( $configuration );

        $offsetX = 0;
        foreach ( $this->_icons as $icon ) {
            $this->_items[] = new jdStarterbarItem( $icon, $offsetX, 64 );
            
            $offsetX += 80;
        } 
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

        $this->add_events( Gdk::BUTTON_PRESS_MASK |
                           Gdk::POINTER_MOTION_MASK );
        $this->connect( "button-press-event", array( $this, "OnMousePress" ) );
        $this->connect( "motion-notify-event", array( $this, "OnMouseMove" ) );

        foreach ( $this->_items as $item ) {
            $item->OnExpose( $gc, $window );
        }
    }

    public function OnMousePress(jdWidget $source, GdkEvent $event) {
        print "CLICK ME\n";
    }

    public function OnMouseMove(jdWidget $source, GdkEvent $event) {
        $scale = 0.0;
        $index = 0;
        
        foreach ( $this->_items as $item ) {
            if ( $item->isInOuterRange( $event ) && $item->scaleForEvent( $event ) > 0.1 ) {
                // Ask for redraw
                $source->queue_draw();
            }
        }
    }
}

class jdStarterbarItem {

    const MAX_SCALE = 0.7;

    private $_icon = null;

    private $_offsetX = 0;

    private $_centerX = 0;
    private $_centerY = 0;

    private $_width  = 0;
    private $_height = 0;

    private $_size = 64;

    private $_scale = 0.0;

    private $_properties = array(
        "Width"  => "_width",
        "Height" => "_height");

    public function __construct( $icon, $offsetX = 0, $size = 64) {
        $this->_icon = GdkPixbuf::new_from_file( $icon );
        $this->_size = (int) $size;

        $this->_offsetX = $offsetX;

        // Total X / Y Space for this item 
        $this->_width  = ceil( $size + ( $size * self::MAX_SCALE ) + 20 );
        $this->_height = ceil( 20 + ( $size * 2 ) ); 

        $this->_centerX = $this->_offsetX + $this->_width / 2;
        $this->_centerY = ( $this->_height / 2 ) + ( $this->_size / 2 ) + 10;
    }

    public function __get( $name ) {
        return ( isset( $this->_properties[$name] ) ? $this->{$this->_properties[$name]} : null );
    }

    public function __set( $name, $value ) {
        if ( isset( $this->_properties[$name] ) ) {
            $this->{$this->_properties[$name]} = $value;
        }
    }

    public function isInOuterRange( GdkEvent $event ) {
        return ( $event->x >= $this->_offsetX && 
                 $event->x <= ( $this->_offsetX + $this->_width ) &&
                 $event->y >= ( $this->_centerY - $this->_size ) && 
                 $event->y <= $this->_height );
    }

    public function scaleForEvent( GdkEvent $event ) {	

	if ( !$this->isInOuterRange( $event ) ) {
            return 0.0;
        }

	$sqrt = sqrt( pow( $this->_centerX - $event->x, 2) + pow( $this->_centerY - $event->y, 2 ) );

        if ( $sqrt > $this->_size ) {
            return 0.0;
        }

        // Check for leaving bottom
        if ( ( $this->_height - $event->y ) <= 0.5 ) {
            $scale = 0.0;
        } else {
            $scale = ( $this->_size - $sqrt ) * self::MAX_SCALE;

            // Is cursor below the center?
            if ( ( $diff = ( $event->y - $this->_centerY ) ) > ( $this->_size / 3 ) ) {
                $scale *= 2 / $diff;
            }

            // Check for minimum change
            if ( abs( $this->_scale - $scale ) <= 0.2 ) {
                return 0.0;
            }
        }

        // Keep current scale info
        $this->_scale = $scale;

        return $scale;
    }

    public function OnExpose( $gc, $window ) {
        $size = ceil( $this->_scale + $this->_size );

        $x = $this->_offsetX + ( ( $this->_width - $size ) / 2 );
        $y = $this->_height - $size;

        $pixbuf = $this->_icon->scale_simple( $size, $size, Gdk::INTERP_NEAREST );
        
	$window->draw_pixbuf( $gc, $pixbuf, 0, 0, $x, $y );
        $window->draw_arc( $gc, false, $this->_offsetX, $this->_centerY - $this->_size, $this->_width, $this->_width, 0, 360 * 64 );
    }

}
