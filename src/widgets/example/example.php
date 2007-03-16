<?php

class jdWidgetExample extends jdWidget 
{
    protected function getSize() 
    {
        return array( 200, 200 );
    }

    public function OnExpose( $gc, $window ) 
    {
        $cmap = $window->get_colormap();

        $gc->set_foreground( $cmap->alloc_color( '#fcaf3e' ) );    
        $window->draw_rectangle( $gc, true, 25, 25, 150, 150 );
    }
}

?>
