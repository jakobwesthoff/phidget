<?php
class jdWidgetFinderTimer
{
    private $time = 0;

    private $callback = null;

    public function __construct( $time, $callback )
    {
        $this->time = $time;
        $this->callback = $callback;
    }
}
