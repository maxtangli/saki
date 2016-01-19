<?php

namespace Saki\Win\Yaku;

use Saki\Util\ArrayLikeObject;
use Saki\Util\Singleton;
use Saki\Util\Utils;
use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;

abstract class Yaku extends Singleton {
    function __toString() {
        // Saki\Win\Yaku\ReachYaku -> Reach
        $cls = get_called_class();
        $s = str_replace('Yaku', '', $cls);
        $s = substr($s, strrpos($s, '\\'));
//        $s = end(explode('\\', $s));
        return $s;
    }

    final function getFanCount($concealed) {
        return $concealed ? $this->getConcealedFanCount() : $this->getNotConcealedFanCount();
    }

    abstract protected function getConcealedFanCount();

    abstract protected function getNotConcealedFanCount();

    final function requireConcealed() {
        return $this->getNotConcealedFanCount() == 0;
    }

    final function isYakuMan() {
        return $this->getConcealedFanCount() >= 13 || $this->getNotConcealedFanCount() >= 13;
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
        return (new ArrayLikeObject($requiredTileSeries))->any(function (TileSeries $tileSeries) use ($allMeldList) {
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