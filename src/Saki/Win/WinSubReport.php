<?php
namespace Saki\Win;

use Saki\Game\SeatWind;
use Saki\Util\Comparable;
use Saki\Util\ComparablePriority;
use Saki\Win\Point\FanAndFu;
use Saki\Win\Point\PointLevel;
use Saki\Win\Point\PointTable;
use Saki\Win\Point\PointTableItem;
use Saki\Win\Yaku\YakuItemList;

/**
 * @package Saki\Win
 */
class WinSubReport {
    //region Comparable impl
    use ComparablePriority;

    function getPriority() {
        // assert fu < 1000, fan < 1000
        return $this->getWinState()->getPriority() * 1000 * 1000
        + $this->getFan() * 1000
        + $this->getFu();
    }
    //endregion

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
     * @return string
     */
    function __toString() {
        list($actor, $winState, $fanAndFu) = [$this->getActor(), $this->getWinState(), $this->getFanAndFu()];
        return "$actor,$winState,$fanAndFu";
    }

    /**
     * @return SeatWind
     */
    function getActor() {
        return $this->actor;
    }

    /**
     * @return WinState
     */
    function getWinState() {
        return $this->winState;
    }

    /**
     * @return YakuItemList
     */
    function getYakuItemList() {
        return $this->yakuItemList;
    }

    /**
     * @return FanAndFu
     */
    function getFanAndFu() {
        return new FanAndFu($this->getFan(), $this->getFu());
    }

    /**
     * @return int
     */
    function getFan() {
        return $this->getYakuItemList()->getTotalFan();
    }

    /**
     * @return int
     */
    function getFu() {
        return $this->fu;
    }

    /**
     * @return PointLevel
     */
    function getPointLevel() {
        return PointLevel::fromFanAndFu($this->getFan(), $this->getFu());
    }

    /**
     * @return PointTableItem
     */
    function getPointItem() {
        return PointTable::create()->getPointItem($this->getFanAndFu());
    }
}