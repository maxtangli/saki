<?php

namespace Saki\Util;

class MsTimer extends Singleton {
    private $begin;

    protected function __construct() {
        $this->begin = microtime(true);
    }

    /**
     * @return float past time in millisecond
     */
    function restart() {
        // note that echo is an time-costly io operation that should be avoid.
        $begin = $this->begin;
        $end = microtime(true);
        $pastSeconds = $end - $begin;
        $pastMs = $pastSeconds * 1000;

        $this->begin = microtime(true);

        return $pastMs;
    }

    function restartWithDump() {
        echo sprintf("%.2f ms\n", $this->restart());
    }

    /**
     * @param callable $f
     * @return float
     */
    function measure(callable $f) {
        $this->restart();
        $f();
        return $this->restart();
    }
}