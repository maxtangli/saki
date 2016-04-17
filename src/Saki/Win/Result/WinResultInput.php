<?php
namespace Saki\Win\Result;

use Saki\Game\PlayerType;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;

/**
 * WinResult's implementation helper which wraps the input data.
 * @package Saki\Win\Result
 */
class WinResultInput {
    /**
     * @param $winnerPair
     * @param array $others
     * @param int $reachPoints
     * @param int $seatWindTurn
     * @return WinResultInput
     */
    static function createTsumo($winnerPair, array $others, int $reachPoints, int $seatWindTurn) {
        list($winner, $fuAndCount) = $winnerPair;
        $itemList = (new ArrayList())
            ->insertLast(
                WinResultInputItem::createWinner($winner, $fuAndCount)
            )
            ->insertLast(
                (new ArrayList($others))->select(function (SeatWind $seatWind) {
                    return WinResultInputItem::createLoser($seatWind);
                })->toArray()
            );
        return new self(true, $itemList, $reachPoints, $seatWindTurn);
    }

    /**
     * @param array $winnerPairs
     * @param SeatWind $loser
     * @param array $others
     * @param int $reachPoints
     * @param int $seatWindTurn
     * @return WinResultInput
     */
    static function createRon(array $winnerPairs, SeatWind $loser, array $others, int $reachPoints, int $seatWindTurn) {
        $itemList = (new ArrayList())
            ->insertLast(
                (new ArrayList($winnerPairs))->select(function (array $winnerPair) {
                    list($winner, $fuAndCount) = $winnerPair;
                    return WinResultInputItem::createWinner($winner, $fuAndCount);
                })->toArray()
            )->insertLast(
                WinResultInputItem::createLoser($loser)
            )->insertLast(
                (new ArrayList($others))->select(function (SeatWind $seatWind) {
                    return WinResultInputItem::createIrrelevant($seatWind);
                })->toArray()
            );
        return new self(false, $itemList, $reachPoints, $seatWindTurn);
    }

    private $isTsumo;
    private $itemList;
    private $reachPoints;
    private $seatWindTurn;

    /**
     * @param bool $isTsumo
     * @param ArrayList $itemList
     * @param int $reachPoints
     * @param int $seatWindTurn
     */
    function __construct(bool $isTsumo, ArrayList $itemList, int $reachPoints, int $seatWindTurn) {
        $this->isTsumo = $isTsumo;
        $this->itemList = $itemList;
        $this->reachPoints = $reachPoints;
        $this->seatWindTurn = $seatWindTurn;
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
    function getReachPoints() {
        return $this->reachPoints;
    }

    /**
     * @return int
     */
    function getSeatWindTurn() {
        return $this->seatWindTurn;
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
        foreach ($itemList as $item) {
            /** @var WinResultInputItem $item */
            $item = $item;
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