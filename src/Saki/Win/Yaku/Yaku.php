<?php

namespace Saki\Win\Yaku;

use Saki\Util\ArrayList;
use Saki\Util\Singleton;
use Saki\Win\Series\Series;
use Saki\Win\WinSubTarget;

/**
 * @package Saki\Win\Yaku
 */
abstract class Yaku extends Singleton {
    /**
     * @return string
     */
    final function __toString() {
        // Saki\Win\Yaku\RiichiYaku -> Riichi
        $cls = get_called_class();
        $s = substr($cls, strrpos($cls, '\\') + 1);
        $s = str_replace('Yaku', '', $s);
        return $s;
    }

    /**
     * @return bool
     */
    final function isYakuMan() {
        return $this->getConcealedFan() >= 13 || $this->getNotConcealedFan() >= 13;
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
    abstract function getConcealedFan();

    /**
     * @return int
     */
    abstract function getNotConcealedFan();

    /**
     * @return bool
     */
    public final function requireConcealed() {
        return $this->getNotConcealedFan() == 0;
    }

    /**
     * @return Series[] design note: seems array is not required ...
     */
    abstract function getRequiredSeries();

    /**
     * @param WinSubTarget $subTarget
     * @return bool
     */
    final function existIn(WinSubTarget $subTarget) {
        return $this->getFan($subTarget) > 0;
    }

    /**
     * @param WinSubTarget $subTarget
     * @return int
     */
    final function getFan(WinSubTarget $subTarget) {
        $matchAll = $this->matchConcealed($subTarget)
            && $this->matchSeries($subTarget)
            && $this->matchOther($subTarget);
        if (!$matchAll) {
            return 0;
        }

        $fan = $subTarget->isConcealed() ? $this->getConcealedFan() : $this->getNotConcealedFan();
        $existCount = $this->getExistCountImpl($subTarget);
        return $fan * $existCount;
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
    final protected function matchConcealed(WinSubTarget $subTarget) {
        return !$this->requireConcealed() || $subTarget->isConcealed();
    }

    /**
     * @param WinSubTarget $subTarget
     * @return bool
     */
    final protected function matchSeries(WinSubTarget $subTarget) {
        $requiredSeries = $this->getRequiredSeries();
        if (empty($requiredSeries)) {
            return true;
        }

        // todo move getSeries into $subTarget
        $allMeldList = $subTarget->getAllMeldList();
        return (new ArrayList($requiredSeries))->any(function (Series $series) use ($allMeldList) {
            return $series->existIn($allMeldList);
        });
    }

    /**
     * @param WinSubTarget $subTarget
     * @return mixed
     */
    abstract protected function matchOther(WinSubTarget $subTarget);

    /**
     * A hook to support dora-type-yaku which requires dynamic fan count.
     * @param WinSubTarget $subTarget
     * @return int
     */
    protected function getExistCountImpl(WinSubTarget $subTarget) {
        return 1;
    }
}