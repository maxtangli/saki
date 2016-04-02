<?php
namespace Saki\Util;

class BenchmarkItem {
    private $name;
    private $callback;
    private $msCost;

    function __construct($name, callable $callback) {
        $this->name = $name;
        $this->callback = $callback;
        $this->msCost = MsTimer::getInstance()->measure($callback);
    }

    function __toString() {
        return sprintf('- %-25s: %7.1f ms.', $this->getName(), $this->getMsCost());
    }

    function getName() {
        return $this->name;
    }

    function getCallback() {
        return $this->callback;
    }

    function getMsCost() {
        return $this->msCost;
    }
}