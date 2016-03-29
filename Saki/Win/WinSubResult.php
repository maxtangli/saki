<?php
namespace Saki\Win;

use Saki\RoundResult\ScoreLevel;
use Saki\RoundResult\ScoreTable;
use Saki\Util\Comparable;
use Saki\Util\Utils;
use Saki\Win\Yaku\YakuItemList;

class WinSubResult {

    use Comparable;

    function compareTo($other) {
        /** @var WinSubResult $other */
        $other = $other;

        $winStateDiff = $this->getWinState()->compareTo($other->getWinState());
        if ($winStateDiff != 0) {
            return $winStateDiff;
        }

        $yakuCountDiff = $this->getFanCount() <=> $other->getFanCount();
        if ($yakuCountDiff != 0) {
            return $yakuCountDiff;
        }

        $fuCountDiff = $this->getFuCount() <=> $other->getFuCount();
        return $fuCountDiff;
    }

    private $winState;
    private $yakuList;
    private $fuCount;

    /**
     * @param WinState $winState
     * @param YakuItemList $yakuList
     * @param int $fuCount
     */
    function __construct(WinState $winState, YakuItemList $yakuList, $fuCount) {
        $this->winState = $winState;
        $this->yakuList = $yakuList;
        $this->fuCount = $fuCount;
    }

    function getWinState() {
        return $this->winState;
    }

    function getYakuList() {
        return $this->yakuList;
    }

    function getFuCount() {
        return $this->fuCount;
    }

    function getFanCount() {
        return $this->getYakuList()->getTotalFanCount();
    }

    function getScoreLevel() {
        return ScoreLevel::fromFanAndFuCount($this->getFanCount(), $this->getFuCount());
    }

    function getScoreItem() {
        return ScoreTable::getInstance()->getScoreItem($this->getFanCount(), $this->getFuCount());
    }
}