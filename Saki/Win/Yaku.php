<?php

namespace Saki\Win;

use Saki\Util\Singleton;

abstract class Yaku extends Singleton {
    function __toString() {
        $cls = get_called_class();
        return str_replace('Yaku', '', $cls);
    }

    final function getFanCount($isExposed) {
        return $isExposed ? $this->getExposedFanCount() : $this->getConcealedFanCount();
    }

    abstract protected function getConcealedFanCount();

    abstract protected function getExposedFanCount();

    final function requireConcealed() {
        return $this->getExposedFanCount() == 0;
    }

    final function isYakuMan() {
        return $this->getConcealedFanCount() >= 13 || $this->getExposedFanCount() >= 13;
    }

    final function existIn(WinAnalyzerSubTarget $subTarget) {
        return (!$this->requireConcealed() || $subTarget->isConcealed())
        && $this->existInImpl($subTarget);
    }

    abstract protected function existInImpl(WinAnalyzerSubTarget $subTarget);

    function getExcludedYakus() {
        return [];
    }

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