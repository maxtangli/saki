<?php

namespace Saki\Win\Yaku;

use Saki\Util\Singleton;
use Saki\Util\Utils;
use Saki\Win\TileSeries\TileSeries;
use Saki\Win\WinSubTarget;

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

    final function existIn(WinSubTarget $subTarget) {
        return $this->matchRequireConcealed($subTarget)
        && $this->matchRequiredTileSeries($subTarget)
        && $this->matchOtherConditions($subTarget);
    }

    final protected function matchRequireConcealed(WinSubTarget $subTarget) {
        return !$this->requireConcealed() || $subTarget->isConcealed();
    }

    final protected function matchRequiredTileSeries(WinSubTarget $subTarget) {
        $requiredTileSeries = $this->getRequiredTileSeries();
        $allMeldList = $subTarget->getAllMeldList();
        return empty($requiredTileSeries) || Utils::array_any($requiredTileSeries, function (TileSeries $tileSeries) use ($allMeldList) {
            return $tileSeries->existIn($allMeldList);
        });
    }

    abstract protected function getRequiredTileSeries();

    abstract protected function matchOtherConditions(WinSubTarget $subTarget);


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