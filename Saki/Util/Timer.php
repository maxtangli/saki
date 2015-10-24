<?php

namespace Saki\Util;

class Timer extends Singleton{
    private $begin;

    protected function __construct() {
        $this->reset();
    }

    function reset() {
        $this->begin = microtime(true);
    }

    function showAndReset() {
        $begin = $this->begin;
        $end = microtime(true);

        $past = $end - $begin;
        $pastMs = $past * 1000;
        $formattedPastMs = round($pastMs, 3);
        echo "$formattedPastMs ms \n";

        $this->begin = $end;
    }

    /**
     * @return Timer
     */
    static function getInstance() {
        return parent::getInstance();
    }
}