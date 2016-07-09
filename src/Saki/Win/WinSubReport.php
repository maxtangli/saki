<?php
namespace Saki\Win;

use Saki\Game\SeatWind;
use Saki\Util\Comparable;
use Saki\Win\Point\FanAndFu;
use Saki\Win\Point\PointLevel;
use Saki\Win\Point\PointTable;
use Saki\Win\Yaku\YakuItemList;

class WinSubReport {
    use Comparable;

    /**
     * @param WinSubReport $other
     * @return bool
     */
    function compareTo($other) {
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

    private $actor;
    private $winState;
    private $yakuItemList;
    private $fu;

    /**
     * @param SeatWind $actor
     * @param WinState $winState
     * @param YakuItemList $yakuItemList
     * @param int $fu
     */
    function __construct(SeatWind $actor, WinState $winState, YakuItemList $yakuItemList, $fu) {
        $this->actor = $actor;
        $this->winState = $winState;
        $this->yakuItemList = $yakuItemList;
        $this->fu = $fu;
    }

    /**
     * @return SeatWind
     */
    function getActor() {
        return $this->actor;
    }
    
    function getWinState() {
        return $this->winState;
    }

    function getYakuItemList() {
        return $this->yakuItemList;
    }

    function getFanAndFu() {
        return new FanAndFu($this->getYakuItemList()->getTotalFan(), $this->fu);
    }

    function getFu() {
        return $this->fu;
    }

    function getFan() {
        return $this->getYakuItemList()->getTotalFan();
    }

    function getPointLevel() {
        return PointLevel::fromFanAndFu($this->getFan(), $this->getFu());
    }

    function getPointItem() {
        return PointTable::create()->getPointItem($this->getFanAndFu());
    }
}