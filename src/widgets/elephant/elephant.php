<?php

class jdWidgetElephant extends jdWidget 
{

    private $pixmaps;
    private $masks;
    private $animationFrame;
    private $direction;
    private $up;

    private $movementX;
    private $movementY;    

    protected function init() 
    {

        $this->pixmaps = array();
        $this->masks = array();
        $this->animationFrame = 0;
        $this->direction = 0;
        $this->up = false;

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
    
        $this->newMovement( 4 );

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
        if ( $this->direction  === 1 ) 
        {
            $this->direction = 0;
            $this->up = !$this->up;
            $this->newMovement( 4 );
        }
        else 
        {            
            $this->direction = 1;
            $this->up = !$this->up;
            $this->newMovement( 16 );
        }

        $this->animationFrame = 0;                                           
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
        $this->collision();
    }

    private function newMovement( $intensity ) 
    {
        $rangeStart = $intensity >> 1;
        $rangeEnd   = $intensity * 2;

        $this->movementX = mt_rand( $rangeStart, $rangeEnd ) * ( $this->direction === 1 ? -1 : 1 );
        $this->movementY = mt_rand( $rangeStart >> 1, $rangeEnd ) * ( $this->up ? -1 : 1 );
    }

    private function collision() 
    {
        $dimension = $this->getSize();
        $collision = false;

        if ( $this->x <= 0 )
        {
            // left collision
            $this->direction =  0;            
            $collision = true;
            echo "LEFT COLLISION \n";
        }

        if ( $this->y <= 40 )
        {
            // top collision
            $this->up = false;
            $collision = true;
            echo "TOP COLLISION \n";
        }

        if ( $this->y >= $this->get_screen()->get_height() - $dimension[1] - 40 ) 
        {
            // bottom collision
            $this->up = true;
            $collision = true;
            echo "BOTTOM COLLISION \n";
        }

        if ( $this->x >= $this->get_screen()->get_width() - $dimension[0] ) 
        {
            // right collision
            $this->direction = 1;
            $collision = true;
            echo "RIGHT COLLISION \n";
        }

        if ( $collision === true ) 
        {
            $this->newMovement( 4 );
        }
    }
}

?>
