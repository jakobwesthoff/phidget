<?php

class jdWidgetElephant extends jdWidget 
{

    private $pixmaps;
    private $masks;
    private $animationFrame;
    private $direction;

    private $movementX;
    private $movementY;    

    protected function init() 
    {

        $this->pixmaps = array();
        $this->masks = array();
        $this->animationFrame = 0;
        $this->direction = 0;

        // Connect to the needed signals
        $this->add_events(
             Gdk::BUTTON_PRESS_MASK
        );

        $this->connect( "button-press-event",  array( $this, "OnMousePress" ) );

        // Load the pictures
        for( $i=0; $i<6; $i++ ) 
        {
            $pixbuf = GdkPixbuf::new_from_file( dirname( __FILE__ ) . '/pics/left_right_' . $i . '.png' );
            list( $this->pixmaps[0][$i], $this->masks[0][$i] ) = $pixbuf->render_pixmap_and_mask();
        }

        for( $i=0; $i<6; $i++ ) 
        {
            $pixbuf = GdkPixbuf::new_from_file( dirname( __FILE__ ) . '/pics/right_left_' . $i . '.png' );
            list( $this->pixmaps[1][$i], $this->masks[1][$i] ) = $pixbuf->render_pixmap_and_mask();
        }
    
        $this->movementX = 4;
        $this->movementY = 2;

        Gtk::timeout_add( 1, array( $this, 'initTimer' ) );
    }

    protected function getSize() 
    {
        return array( 98, 60 );
    }

    private function nextFrame() 
    {
        $this->animationFrame = $this->animationFrame >= 5
                       ? ( 0 )
                       : ( $this->animationFrame + 1 );
    }

    private function changeDirection() 
    {
        $this->direction = $this->direction == 1
                           ? ( 0 )
                           : ( 1 );
        $this->animationFrame = 0;                           
        $this->movementX = -1 * $this->movementX;
    }

    public function expose_event( jdWidget $window, GdkEvent $event )
    {
        $gdkwindow = $event->window;
        $gc = new GdkGC( $gdkwindow );

        return $this->OnExpose( $gc, $event );
    }

    public function OnExpose( GdkGC $gc, GdkEvent $event ) 
    {
        $event->window->shape_combine_mask( $this->masks[$this->direction][$this->animationFrame], 0, 0 );
        $event->window->draw_drawable( $gc,$this->pixmaps[$this->direction][$this->animationFrame], 0, 0, 0, 0, 98, 60 );        
    }

    public function OnMousePress( jdWidget $source, GdkEvent $event )
    {
        $this->changeDirection();
    }

    public function initTimer() 
    {
        $this->set_keep_below( false );
        $this->set_keep_above( true );
        $this->moveit();
    }

    public function moveit() 
    {
        $this->move( $this->x + $this->movementX, $this->y + $this->movementY );
        $this->nextFrame();
        Gtk::timeout_add( 140, array( $this, 'moveit' ) );
    }
}

?>
