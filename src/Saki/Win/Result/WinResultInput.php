<?php
namespace Saki\Win\Result;

use Saki\Game\PlayerType;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
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
     * @return WinResultInput
     */
    static function createTsumo($winnerPair, array $others, int $riichiPoints, int $seatWindTurn, WinReport $winReportForView = null) {
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
        return new self(true, $itemList, $riichiPoints, $seatWindTurn, $winReportList);
    }

    /**
     * @param array $winnerPairs
     * @param SeatWind $loser
     * @param array $others
     * @param int $riichiPoints
     * @param int $seatWindTurn
     * @param array $winReportsForView
     * @return WinResultInput
     */
    static function createRon(array $winnerPairs, SeatWind $loser, array $others, int $riichiPoints, int $seatWindTurn, array $winReportsForView = null) {
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
        return new self(false, $itemList, $riichiPoints, $seatWindTurn, $winReportList);
    }

    private $isTsumo;
    private $itemList;
    private $riichiPoints;
    private $seatWindTurn;
    private $winReportList;

    /**
     * @param bool $isTsumo
     * @param ArrayList $itemList
     * @param int $riichiPoints
     * @param int $seatWindTurn
     * @param ArrayList $winReportList
     */
    protected function __construct(bool $isTsumo, ArrayList $itemList, int $riichiPoints, int $seatWindTurn, ArrayList $winReportList) {
        $this->isTsumo = $isTsumo;
        $this->itemList = $itemList;
        $this->riichiPoints = $riichiPoints;
        $this->seatWindTurn = $seatWindTurn;
        $this->winReportList = $winReportList;
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
     * @return ResultType
     */
    function getResultType() {
        return ResultType::create(
            $this->isTsumo()
                ? ResultType::WIN_BY_SELF
                : ResultType::WIN_BY_OTHER
        );
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
        return $this->getItemList()->getSingle(function (WinResultInputItem $item) use ($seatWind) {
            return $item->getSeatWind() == $seatWind;
        });
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
        return $this->getItemList()->getCopy()->where(function (WinResultInputItem $item) {
            return $item->isWinner();
        });
    }

    /**
     * @return int
     */
    function getWinnerCount() {
        return $this->getItemList()->getCount(function (WinResultInputItem $item) {
            return $item->isWinner();
        });
    }

    /**
     * @return int
     */
    function getLoserCount() {
        return $this->getItemList()->getCount(function (WinResultInputItem $item) {
            return $item->isLoser();
        });
    }
}