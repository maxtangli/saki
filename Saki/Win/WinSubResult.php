<?php
namespace Saki\Win;

use Saki\RoundResult\ScoreLevel;
use Saki\RoundResult\ScoreTable;
use Saki\Util\ArrayLikeObject;
use Saki\Util\Utils;
use Saki\Win\Yaku\YakuList;

class WinSubResult {

    static function getComparator() {
        $f = function(WinSubResult $a, WinSubResult $b) {
            // compare by winState, yakuCount, fuCount
            $winStateDiff = $a->getWinState()->compareTo($b->getWinState());
            if ($winStateDiff != 0) {
                return $winStateDiff;
            }

            $yakuCountDiff = Utils::sgn($a->getFanCount() - $b->getFanCount());
            if ($yakuCountDiff != 0) {
                return $yakuCountDiff;
            }

            $fuCountDiff = Utils::sgn($a->getFuCount() - $b->getFuCount());
            return $fuCountDiff;
        };
        return $f;
    }

    function compareTo(WinSubResult $other) {
        $f = $this->getComparator();
        return $f($this, $other);
    }

    private $winState;
    private $yakuList;
    private $fuCount;

    /**
     * @param WinState $winState
     * @param YakuList $yakuList
     * @param int $fuCount
     */
    function __construct(WinState $winState, YakuList $yakuList, $fuCount) {
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
        return $this->getYakuList()->getFanCount();
    }

    function getScoreLevel() {
        return ScoreLevel::fromFanAndFuCount($this->getFanCount(), $this->getFuCount());
    }

    function getScoreItem() {
        return ScoreTable::getInstance()->getScoreItem($this->getFanCount(), $this->getFuCount());
    }
}