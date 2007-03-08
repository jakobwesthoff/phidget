<?php

class jdWidgetTest extends jdWidget 
{
    public function OnExpose( $gc, $window ) 
    {
        $cmap = $window->get_colormap();

        $gc->set_foreground( $cmap->alloc_color( '#fcaf3e' ) );    
        $window->draw_rectangle( $gc, true, 25, 25, 150, 150 );
    }
}

?>
