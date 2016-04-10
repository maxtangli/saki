<?php

namespace Saki\Win\Yaku;

use Saki\Util\ArrayList;
use Saki\Util\Singleton;
use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;

/**
 * @package Saki\Win\Yaku
 */
abstract class Yaku extends Singleton {
    /**
     * @return string
     */
    final function __toString() {
        // Saki\Win\Yaku\ReachYaku -> Reach
        $cls = get_called_class();
        $s = substr($cls, strrpos($cls, '\\') + 1);
        $s = str_replace('Yaku', '', $s);
        return $s;
    }

    /**
     * @return bool
     */
    final function isYakuMan() {
        return $this->getConcealedFanCount() >= 13 || $this->getNotConcealedFanCount() >= 13;
    }

    /**
     * @return bool
     */
    final function isDoraTypeYaku() {
        $cls = get_called_class();
        $filenameExistDora = (strpos($cls, 'Dora') !== false);
        return $filenameExistDora;
    }

    /**
     * @return int
     */
    abstract function getConcealedFanCount();

    /**
     * @return int
     */
    abstract function getNotConcealedFanCount();

    /**
     * @return bool
     */
    public final function requireConcealed() {
        return $this->getNotConcealedFanCount() == 0;
    }

    /**
     * @return TileSeries[] design note: seems array is not required ...
     */
    abstract function getRequiredTileSeries();
    
    /**
     * @param WinSubTarget $subTarget
     * @return bool
     */
    final function existIn(WinSubTarget $subTarget) {
        return $this->getFanCount($subTarget) > 0;
    }

    /**
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

    /**
     * @return Yaku[]
     */
    function getExcludedYakus() {
        return [];
    }

    /**
     * @param WinSubTarget $subTarget
     * @return bool
     */
    final protected function matchRequireConcealed(WinSubTarget $subTarget) {
        return !$this->requireConcealed() || $subTarget->isConcealed();
    }

    /**
     * @param WinSubTarget $subTarget
     * @return bool
     */
    final protected function matchRequiredTileSeries(WinSubTarget $subTarget) {
        $requiredTileSeries = $this->getRequiredTileSeries();
        if (empty($requiredTileSeries)) {
            return true;
        }

        // todo move getTileSeries into $subTarget
        $allMeldList = $subTarget->getAllMeldList();
        return (new ArrayList($requiredTileSeries))->any(function (TileSeries $tileSeries) use ($allMeldList) {
            return $tileSeries->existIn($allMeldList);
        });
    }

    /**
     * @param WinSubTarget $subTarget
     * @return mixed
     */
    abstract protected function matchOtherConditions(WinSubTarget $subTarget);

    /**
     * A hook to support dora-type-yaku which requires dynamic fan count.
     * @param WinSubTarget $subTarget
     * @return int
     */
    protected function getExistCountImpl(WinSubTarget $subTarget) {
        return 1;
    }
}