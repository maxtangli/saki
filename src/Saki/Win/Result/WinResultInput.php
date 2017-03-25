<?php

namespace Saki\Win\Result;

use Saki\Game\PlayerType;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Win\Pao\PaoList;
use Saki\Win\WinReport;

/**
 * WinResult's implementation helper which wraps the input data.
 * @package Saki\Win\Result
 */
class WinResultInput {
    /**
     * @param $winnerPair
     * @param array $others
     * @param int $riichiPoints
     * @param int $seatWindTurn
     * @param WinReport|null $winReportForView
     * @param PaoList $paoList
     * @return WinResultInput
     */
    static function createTsumo($winnerPair, array $others, int $riichiPoints, int $seatWindTurn,
                                WinReport $winReportForView = null, PaoList $paoList = null) {
        list($winner, $fuAndCount) = $winnerPair;

        $winner = WinResultInputItem::createWinner($winner, $fuAndCount);

        $toLoser = function (SeatWind $seatWind) {
            return WinResultInputItem::createLoser($seatWind);
        };
        $loserList = (new ArrayList($others))->select($toLoser);

        $itemList = (new ArrayList())
            ->insertLast($winner)
            ->concat($loserList);
        $winReportList = $winReportForView !== null ? new ArrayList([$winReportForView]) : new ArrayList();
        return new self(true, $itemList, $riichiPoints, $seatWindTurn, $winReportList, $paoList ?? new PaoList());
    }

    /**
     * @param array $winnerPairs
     * @param SeatWind $loser
     * @param array $others
     * @param int $riichiPoints
     * @param int $seatWindTurn
     * @param array $winReportsForView
     * @param PaoList $paoList
     * @return WinResultInput
     */
    static function createRon(array $winnerPairs, SeatWind $loser, array $others, int $riichiPoints, int $seatWindTurn,
                              array $winReportsForView = null, PaoList $paoList = null) {
        $toWinner = function (array $winnerPair) {
            list($winner, $fuAndCount) = $winnerPair;
            return WinResultInputItem::createWinner($winner, $fuAndCount);
        };
        $winnerList = (new ArrayList($winnerPairs))->select($toWinner);

        $loser = WinResultInputItem::createLoser($loser);

        $toIrrelevant = function (SeatWind $seatWind) {
            return WinResultInputItem::createIrrelevant($seatWind);
        };
        $irrelevantList = (new ArrayList($others))->select($toIrrelevant);

        $itemList = (new ArrayList())
            ->concat($winnerList)
            ->insertLast($loser)
            ->concat($irrelevantList);
        $winReportList = new ArrayList($winReportsForView ?? []);
        return new self(false, $itemList, $riichiPoints, $seatWindTurn, $winReportList, $paoList ?? new PaoList());
    }

    private $isTsumo;
    private $itemList;
    private $riichiPoints;
    private $seatWindTurn;
    private $winReportList;
    private $paoList;

    /**
     * @param bool $isTsumo
     * @param ArrayList $itemList
     * @param int $riichiPoints
     * @param int $seatWindTurn
     * @param ArrayList $winReportList
     * @param PaoList $paoList
     */
    private function __construct(bool $isTsumo, ArrayList $itemList, int $riichiPoints, int $seatWindTurn,
                                 ArrayList $winReportList, PaoList $paoList) {
        $this->isTsumo = $isTsumo;
        $this->itemList = $itemList;
        $this->riichiPoints = $riichiPoints;
        $this->seatWindTurn = $seatWindTurn;
        $this->winReportList = $winReportList;
        $this->paoList = $paoList;
    }

    /**
     * @return bool
     */
    function isTsumo() {
        return $this->isTsumo;
    }

    /**
     * @return ArrayList An ArrayList of WinResultInputItem.
     */
    function getItemList() {
        return $this->itemList;
    }

    /**
     * @return int
     */
    function getRiichiPoints() {
        return $this->riichiPoints;
    }

    /**
     * @return int
     */
    function getSeatWindTurn() {
        return $this->seatWindTurn;
    }

    /**
     * @return ArrayList
     */
    function getWinReportList() {
        return $this->winReportList;
    }

    /**
     * @return PaoList
     */
    function getPaoList() {
        return $this->paoList;
    }

    /**
     * @return ResultType
     */
    function getResultType() {
        if ($this->isTsumo()) {
            $v = ResultType::TSUMO_WIN;
        } else {
            switch ($this->getWinnerCount()) {
                case 1:
                    $v = ResultType::RON_WIN;
                    break;
                case 2:
                    $v = ResultType::DOUBLE_RON_WIN;
                    break;
                default:
                    throw new \LogicException();
            }
        }

        return ResultType::create($v);
    }

    /**
     * @return PlayerType
     */
    function getPlayerType() {
        return PlayerType::create($this->getItemList()->count());
    }

    /**
     * @param SeatWind $seatWind
     * @return WinResultInputItem
     */
    function getItem(SeatWind $seatWind) {
        $isSeatWind = function (WinResultInputItem $item) use ($seatWind) {
            return $item->getSeatWind() == $seatWind;
        };
        return $this->getItemList()->getSingle($isSeatWind);
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function isNearestWinner(SeatWind $seatWind) {
        // double $itemList to ensure finding in one loop
        $itemList = $this->getItemList()->getCopy()
            ->insertLast($this->getItemList()->toArray());
        $loserLocated = false;
        /** @var WinResultInputItem $item */
        foreach ($itemList as $item) {
            if ($loserLocated && $item->isWinner()) {
                return $item->getSeatWind() == $seatWind;
            } elseif (!$loserLocated && $item->isLoser()) {
                $loserLocated = true;
            } else {
                // do nothing
            }
        }
        throw new \LogicException();
    }

    /**
     * @return ArrayList An ArrayList of WinResultInputItem.
     */
    function getWinnerItemList() {
        $isWinner = function (WinResultInputItem $item) {
            return $item->isWinner();
        };
        return $this->getItemList()->getCopy()->where($isWinner);
    }

    /**
     * @return int
     */
    function getWinnerCount() {
        $isWinner = function (WinResultInputItem $item) {
            return $item->isWinner();
        };
        return $this->getItemList()->getCount($isWinner);
    }

    /**
     * @return int
     */
    function getLoserCount() {
        $isLoser = function (WinResultInputItem $item) {
            return $item->isLoser();
        };
        return $this->getItemList()->getCount($isLoser);
    }
}