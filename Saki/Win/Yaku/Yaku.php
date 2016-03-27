<?php

namespace Saki\Win\Yaku;

use Saki\Util\ArrayLikeObject;
use Saki\Util\Singleton;
use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;

/**
 * note: to be more clear, separate interface and impl-helpers.
 * @package Saki\Win\Yaku
 */
abstract class Yaku extends Singleton {
    final function __toString() {
        // Saki\Win\Yaku\ReachYaku -> Reach
        $cls = get_called_class();
        $s = substr($cls, strrpos($cls, '\\') + 1);
        $s = str_replace('Yaku', '', $s);
        return $s;
    }

    final function isYakuMan() {
        return $this->getConcealedFanCount() >= 13 || $this->getNotConcealedFanCount() >= 13;
    }

    final function isDoraTypeYaku() {
        $cls = get_called_class();
        $filenameExistDora = (strpos($cls, 'Dora') !== false);
        return $filenameExistDora;
    }

    abstract protected function getConcealedFanCount();

    abstract protected function getNotConcealedFanCount();

    final protected function requireConcealed() {
        return $this->getNotConcealedFanCount() == 0;
    }

    final function existIn(WinSubTarget $subTarget) {
        return $this->getFanCount($subTarget) > 0;
    }

    /**
     * goal: support dora-type-yaku.
     * @param WinSubTarget $subTarget
     * @return int
     */
    final function getFanCount(WinSubTarget $subTarget) {
        $matchAll = $this->matchRequireConcealed($subTarget)
            && $this->matchRequiredTileSeries($subTarget)
            && $this->matchOtherConditions($subTarget);
        if (!$matchAll) {
            return 0;
        }

        $fanCount = $subTarget->isConcealed() ? $this->getConcealedFanCount() : $this->getNotConcealedFanCount();
        $existCount = $this->getExistCountImpl($subTarget);
        return $fanCount * $existCount;
    }

    protected function getExistCountImpl(WinSubTarget $subTarget) {
        return 1;
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