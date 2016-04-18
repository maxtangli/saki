<?php
namespace Saki\Win\Result;

use Saki\Game\SeatWind;
use Saki\Win\Point\FanAndFu;
use Saki\Win\Point\PointTable;
use Saki\Win\Point\PointTableItem;

/**
 * @package Saki\Win\Result
 */
class WinResultInputItem {
    /**
     * @param SeatWind $seatWind
     * @param FanAndFu $fanAndFu
     * @return WinResultInputItem
     */
    static function createWinner(SeatWind $seatWind, FanAndFu $fanAndFu) {
        return new self($seatWind, true, $fanAndFu);
    }

    /**
     * @param SeatWind $seatWind
     * @return WinResultInputItem
     */
    static function createLoser(SeatWind $seatWind) {
        return new self($seatWind, false);
    }

    /**
     * @param SeatWind $seatWind
     * @return WinResultInputItem
     */
    static function createIrrelevant(SeatWind $seatWind) {
        return new self($seatWind, null);
    }

    private $seatWind;
    /** @var bool|null turn|false|null means Winner|Loser|Irrelevant */
    private $winnerFlag;
    private $pointTableItem;

    /**
     * @param SeatWind $seatWind
     * @param bool|null $winnerFlag
     * @param FanAndFu|null $fanAndFu
     */
    protected function __construct(SeatWind $seatWind, $winnerFlag, FanAndFu $fanAndFu = null) {
        // design note: need no validation since assured by create() methods.
        $this->seatWind = $seatWind;
        $this->winnerFlag = $winnerFlag;
        $this->pointTableItem = $fanAndFu !== null
            ? PointTable::create()->getPointItem($fanAndFu)
            : null;
    }

    /**
     * @return SeatWind
     */
    function getSeatWind() {
        return $this->seatWind;
    }

    /**
     * @return boolean
     */
    function isWinner() {
        return $this->winnerFlag === true;
    }

    /**
     * @return bool
     */
    function isLoser() {
        return $this->winnerFlag === false;
    }

    /**
     * @return bool
     */
    function isIrrelevant() {
        return $this->winnerFlag === null;
    }

    /**
     * @return bool
     */
    function isDealer() {
        return $this->getSeatWind()->isDealer();
    }

    /**
     * @return PointTableItem
     */
    function getPointTableItem() {
        if (!$this->isWinner()) {
            throw new \BadMethodCallException();
        }
        return $this->pointTableItem;
    }
}