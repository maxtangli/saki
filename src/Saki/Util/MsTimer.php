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
     * @param int $nTodo
     * @return float
     */
    function measure(callable $f, int $nTodo = 1) {
        $this->restart();
        while ($nTodo-- > 0) $f();
        return $this->restart();
    }

    function measureAverage(callable $f, int $nTodo) {
        return $this->measure($f, $nTodo) / $nTodo;
    }

    function vs(callable $origin, callable $optimize) {
        $nTodo = 100;
        $originAverage = $this->measureAverage($origin, $nTodo);
        $optimizeAverage = $this->measureAverage($optimize, $nTodo);
        $improvementRatio = $optimizeAverage / $originAverage;
        return sprintf('%.3fms => %.3fms, %.2f.', $originAverage, $optimizeAverage, $improvementRatio);
    }
}