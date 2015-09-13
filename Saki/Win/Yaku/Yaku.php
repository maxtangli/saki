<?php

namespace Saki\Win\Yaku;

use Saki\Util\ArrayLikeObject;
use Saki\Util\Singleton;
use Saki\Util\Utils;
use Saki\Win\TileSeries;
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
        if (empty($requiredTileSeries)) {
            return true;
        }

        $allMeldList = $subTarget->getAllMeldList();
        $l = new ArrayLikeObject($requiredTileSeries);
        return $l->any(function (TileSeries $tileSeries) use ($allMeldList) {
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