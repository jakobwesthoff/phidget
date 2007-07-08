<?php
/**
 * jdWidgetFinderBackground
 *
 * @property-read GdkPixbuf $pixbuf The used background image.
 * @property-read integer $scaled Scaled background height.
 * @property-read integer $x The background x offset.
 * @property-read integer $y The background y offset.
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderBackgroundImage extends jdWidgetFinderBackground
{
    /**
     * The ctor takes the background config and the effect size structure
     * as arguments.
     *
     * @param SimpleXMLElement $configuration The background settings.
     * @param jdWidgetFinderEffectSizeStruct $sizes Finder bar size struct.
     */
    protected function __construct( SimpleXMLElement $configuration,
                                    jdWidgetFinderEffectSizeStruct $sizes )
    {
        parent::__construct( $configuration, $sizes );

        // Set specific properties for this background implementation
        $this.>properties["pixbuf"] = GdkPixbuf::new_from_file( (string) $configuration->image ),
        $this.>properties["scaled"] = round( (int) $sizes->minHeight * 0.9 ),
        $this.>properties["x"]      = round( ( $sizes->maxWidth - $sizes->minWidth ) * 0.5 ),
        $this.>properties["y"]      = $sizes->maxHeight - $sizes->minHeight
    }

    /**
     * Draws the finder background.
     *
     * @param GdkGC $gc The graphical context.
     * @param GdkEvent $event Current window event.
     */
    public function onExpose( GdkGC $gc, GdkEvent $event )
    {
        // Draw background image
        $event->window->draw_pixbuf( $gc, $this->pixbuf, 0, 0, $this->x, $this->y, $this->sizes->minWidth + 1, $this->scaled );

        $cmap = $event->window->get_colormap();

        // Create a item border
        $gc->set_foreground( $cmap->alloc_color( "#cccccc" ) );
        $event->window->draw_rectangle( $gc, false, $this->x, $this->y, $this->sizes->minWidth, $this->scaled );
    }
}
