<?php
namespace Saki\Win\Result;

use Saki\Game\SeatWind;

/**
 * @package Saki\Win\Result
 */
class NewWinResult extends NewResult {
    private $input;

    /**
     * @param WinResultInput $input
     */
    function __construct(WinResultInput $input) {
        parent::__construct($input->getPlayerType(), $input->getResultType());
        $this->input = $input;
    }

    /**
     * @return WinResultInput
     */
    protected function getInput() {
        return $this->input;
    }

    //region impl
    function isKeepDealer() {
        // Dealer is winner
        return $this->getInput()->getItem(SeatWind::createEast())
            ->isWinner();
    }

    function getPointChange(SeatWind $seatWind) {
        return $this->getTablePointChange($seatWind)
        + $this->getReachPointsChange($seatWind)
        + $this->getSeatWindTurnPointChange($seatWind);
    }

    //endregion

    function getTablePointChange(SeatWind $seatWind) {
        // winner: $pointItem->getWinnerChange()
        // loser: sum each winner.$pointItem->getLoserChange()
        // irrelevant: 0
        $input = $this->getInput();
        $isWinBySelf = $input->isWinBySelf();
        $item = $input->getItem($seatWind);
        if ($item->isWinner()) {
            $winnerItem = $item;
            return $winnerItem->getPointTableItem()
                ->getWinnerPointChange($isWinBySelf, $winnerItem->isDealer());
        } elseif ($item->isLoser()) {
            $loserItem = $item;
            $selector = function (WinResultInputItem $winnerItem) use ($isWinBySelf, $loserItem) {
                return $winnerItem->getPointTableItem()
                    ->getLoserPointChange($isWinBySelf, $winnerItem->isDealer(), $loserItem->isDealer());
            };
            return $input->getWinnerItemList()->getSum($selector);
        } else {
            return 0;
        }
    }

    /**
     * @param SeatWind $seatWind
     * @return int
     */
    function getReachPointsChange(SeatWind $seatWind) {
        // nearest winner: $reachPoints
        // not nearest winner, loser, irrelevant: 0
        $input = $this->getInput();
        return $input->isNearestWinner($seatWind)
            ? $input->getReachPoints()
            : 0;
    }

    /**
     * @param SeatWind $seatWind
     * @return int
     */
    function getSeatWindTurnPointChange(SeatWind $seatWind) {
        // winner: $seatWindTurn * 300 / winnerCount
        // loser: - $seatWindTurn * 300 / loserCount
        // irrelevant: 0
        $input = $this->getInput();
        $item = $input->getItem($seatWind);
        $total = $input->getSeatWindTurn() * 300; // always dividable by 1/2/3
        if ($item->isWinner()) {
            return intval($total / $input->getWinnerCount());
        } elseif ($item->isLoser()) {
            return -intval($total / $input->getLoserCount());
        } else {
            return 0;
        }
    }
}