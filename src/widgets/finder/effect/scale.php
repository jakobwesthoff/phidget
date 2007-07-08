<?php
/**
 * jdWidgetFinderEffectScale
 *
 * @version //autogen//
 * @copyright Copyright (C) 2007 Jakob Westhoff, Manuel Pichler.
 *            All rights reserved.
 * @author Jakob Westhoff <jakob@php.net>
 * @author Manuel Pichler <mapi@manuel-pichler.de>
 * @license GPL
 */
class jdWidgetFinderEffectScale extends jdWidgetFinderEffect
{
    private $size = 0;

    private $zoom = 0;

    private $space = 0;

    private $range = 0;

    /**
     * The item scale size. This value is needed more than once, so we
     * calculate it in the ctor.
     *
     * @type integer
     * @var integer $scaleSize
     */
    private $scaleSize = 0;

    protected function __construct( array $items, SimpleXMLElement $configuration )
    {
        parent::__construct( $items, $configuration );

        $this->size  = (int) $this->configuration->size;
        $this->zoom  = (int) $this->configuration->zoom;
        $this->space = (int) $this->configuration->space;

        $this->scaleSize = $this->zoom - $this->size;

        $this->range = (
            1 - ( $this->size / $this->zoom ) * ( $this->scaleSize / ( 2 * $this->size ) )
        );

        // Position all items
        $this->process( 0, 0 );
    }

    public function onExpose( GdkGC $gc, GdkEvent $event )
    {
        $area = $event->area;

        // Draw every icon to the widget surface
        foreach( $this->items as $item )
        {
            if ( $item->x < $area->x || $item->x > ( $area->x + $area->width ) )
            {
                continue;
            }

            $item->draw( $gc, $event->window );
        }
    }

    public function onMouseMove( GdkEvent $event )
    {
        return $this->process( $event->x, $event->y );
    }

    public function onMousePress( GdkEvent $event )
    {
        // Find the matching item
        foreach ( $this->items as $item )
        {
            // Calculate item x min/max range
            $maxX = $item->x + $item->width;

            if ( $event->x > $maxX || $event->x < $item->x )
            {
                continue;
            }

            foreach ( $this->getRegisteredAnimations( $item ) as $anim )
            {
                new jdWidgetFinderAnimationTimer( $event->window, $anim, $item );
            }

            $item->onMouseClick( $event );
            break;
        }
    }

    public function onMouseRelease( GdkEvent $event )
    {
        $this->process( 0, 0 );
    }

    /**
     * Calculates the minimum and maximum size for all finder items.
     *
     * NOTE: We must use the configuration property, because the local
     * $size, $zoom, $space properties are set after this method is
     * called.
     *
     * @return jdWidgetFinderEffectSizeStruct
     */
    protected function calculateSizes()
    {
        // Calculate minimum width
        $minWidth = ( count( $this->items ) * ( (int) $this->configuration->size + (int) $this->configuration->space ) ) - $this->configuration->space;

        // Calculate maximum width
        $maxWidth = $minWidth + (int) $this->configuration->zoom;

        return new jdWidgetFinderEffectSizeStruct(
                        $minWidth, (int) $this->configuration->size,
                        $maxWidth, (int) $this->configuration->zoom
                    );
    }

    protected function process( $eventX, $eventY )
    {
        // We added a space behind the last item, which isn't really there
        $realWidth = $this->resizeItems( $eventX, $eventY );

        $repaintOffsets = new stdClass();
        $repaintOffsets->start = -1;
        $repaintOffsets->end   = 0;

        // Calc new xoffset based on the new width of the bar
        $offsetX = round( ( $this->sizes->maxWidth - $realWidth ) * 0.5 );
        // Correct the overlapping positions and center the bar correctly
        // xoffset is left border based
        foreach ( $this->items as $i => $item )
        {
            // Check for a position or size change
            if ( $offsetX !== $item->x || $this->size !== $item->width )
            {
                if ( $repaintOffsets->start === -1 )
                {
                    $repaintOffsets->start = ( $i === 0 ? 0 : $offsetX );
                }
                $repaintOffsets->end = $offsetX + $item->width;
            }

            $item->x  = $offsetX;
            $offsetX += $item->width + $this->space;
        }

        // Check for right repaint border
        if ( $repaintOffsets->end > $realWidth )
        {
            $repaintOffsets->end = $this->sizes->maxWidth;
        }

        return new GdkRectangle(
             $repaintOffsets->start, 0,
             $repaintOffsets->end - $repaintOffsets->start, $this->zoom
        );
    }

    protected function resizeItems( $eventX, $eventY )
    {
        $width = 0;

        $scalings = $this->calculateScalings( $eventX, $eventY );

        foreach ( $this->items as $i => $item )
        {
            // Resize a scaled item
            if ( isset( $scalings[$i] ) )
            {
                $size = round( $this->size + ( $this->scaleSize * $scalings[$i] ) );

                $item->width  = $size;
                $item->height = $size;
                $item->y      = $this->zoom - $item->height;
            }
            else
            {
                $item->width  = $this->size;
                $item->height = $this->size;
                $item->y      = $this->zoom - $this->size;
            }

            $width += ( $item->width + $this->space );
        }

        // We added a space behind the last item, which isn't really there
        return ( $width - $this->space );
    }

    /**
     * This method calculates the scale factor for all scaling items.
     *
     * @param integer $x The mouse pointer x offset.
     * @param integer $y The mouse pointer y offset.
     * @return array The scale factors for all affected items. This
     * array uses the same index as the related item.
     */
    protected function calculateScalings( $x, $y )
    {
        // All scalings
        $scalings = array();

        // Calculate the sensitive area, where items are scaling
        $area = ( ( $this->zoom * $this->range ) + $this->space );

        foreach ( $this->items as $i => $item )
        {
            // Check that the current item is in the sensitive area
            if ( ( ( $item->x - $area ) > $x ) || ( ( $item->x + $area ) < $x ) )
            {
                //continue;
            }

            $scaleY = ( $y / $this->scaleSize );
            $scaleY = ( $scaleY > 1.0 ? 1.0 : $scaleY );

            $scaleX = ( $area - abs( ( $item->x + ( $item->width * 0.5 ) ) - $x ) ) / $area;

            // Really strange behaviour, we can get a negative event
            // value, so we must check this here.
            if ( ( $scale = round( ( $scaleX * $scaleY ), 2 ) ) > 0.0 )
            {
                // Calculate complete scaling for both axis
                $scalings[$i] = $scale;
            }
        }

        // Correct scaling errors that can happen in the center of an
        // item, where both neighbor items can match or not
        if ( ( $sum = array_sum( $scalings ) ) != 0.0 && abs( $sum - 1.0 ) < 0.05 )
        {
            $rescale = ( ( 1.0 - $sum ) / count( $scalings ) );

            foreach ( $scalings as $i => $scaling )
            {
                $scalings[$i] = $scaling + $rescale;
            }
        }

        return $scalings;
    }
}
