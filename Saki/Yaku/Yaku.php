<?php

namespace Saki\Yaku;

use Saki\Util\Singleton;
use Saki\Win\WinAnalyzerSubTarget;

abstract class Yaku extends Singleton {
    function __toString() {
        $cls = get_called_class();
        return str_replace('Yaku', '', $cls);
    }

    final function getFanCount($isExposed) {
        return $isExposed ? $this->getExposedFanCount() : $this->getConcealedFanCount();
    }

    abstract function getConcealedFanCount();

    abstract function getExposedFanCount();

    final function requireConcealed() {
        return $this->getExposedFanCount() == 0;
    }

    final function existIn(WinAnalyzerSubTarget $subTarget) {
        return (!$this->requireConcealed() || $subTarget->isConcealed())
        && $this->existInImpl($subTarget);
    }

    abstract protected function existInImpl(WinAnalyzerSubTarget $subTarget);

    /**
     * @return Yaku
     */
    static function getInstance() {
        return parent::getInstance();
    }
}

/*

class StringKeyMap {
    private $a;

    function __construct() {
        $this->a = [];
    }

    function get($key) {
        return $this->a[$this->toNormalizedKey($key)];
    }

    function existKey($key) {
        return isset($this->a[$this->toNormalizedKey($key)]);
    }

    function set($key, $value) {
        $this->a[$this->toNormalizedKey($key)] = $value;
    }

    function add($key, $value) {
        if ($this->existKey($key)) {
            throw new \InvalidArgumentException();
        }
        $this->set($key, $value);
    }

    static function toNormalizedKey($key) {
        return (string) $key;
    }
}



*/