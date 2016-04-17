<?php
namespace Saki\Win;

use Saki\Util\Comparable;
use Saki\Win\Point\FanAndFu;
use Saki\Win\Point\PointLevel;
use Saki\Win\Point\PointTable;
use Saki\Win\Yaku\YakuItemList;

class WinSubReport {
    use Comparable;

    function compareTo($other) {
        /** @var WinSubReport $other */
        $other = $other;

        $winStateDiff = $this->getWinState()->compareTo($other->getWinState());
        if ($winStateDiff != 0) {
            return $winStateDiff;
        }

        $yakuCountDiff = $this->getFan() <=> $other->getFan();
        if ($yakuCountDiff != 0) {
            return $yakuCountDiff;
        }

        $fuDiff = $this->getFu() <=> $other->getFu();
        return $fuDiff;
    }

    private $winState;
    private $yakuList;
    private $fu;

    /**
     * @param WinState $winState
     * @param YakuItemList $yakuList
     * @param int $fu
     */
    function __construct(WinState $winState, YakuItemList $yakuList, $fu) {
        $this->winState = $winState;
        $this->yakuList = $yakuList;
        $this->fu = $fu;
    }

    function getWinState() {
        return $this->winState;
    }

    function getYakuList() {
        return $this->yakuList;
    }

    function getFanAndFu() {
        return new FanAndFu($this->getYakuList()->getTotalFan(), $this->fu);
    }
    
    function getFu() {
        return $this->fu;
    }

    function getFan() {
        return $this->getYakuList()->getTotalFan();
    }

    function getPointLevel() {
        return PointLevel::fromFanAndFu($this->getFan(), $this->getFu());
    }

    function getPointItem() {
        return PointTable::create()->getPointItem($this->getFanAndFu());
    }
}