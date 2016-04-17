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
        return $this->getTableChange($seatWind)
        + $this->getReachChange($seatWind)
        + $this->getSeatChange($seatWind);
    }

    //endregion

    /**
     * @param SeatWind $seatWind
     * @return int
     */
    function getTableChange(SeatWind $seatWind) {
        // winner: $pointItem->getWinnerChange()
        // loser: sum each winner.$pointItem->getLoserChange()
        // irrelevant: 0
        $input = $this->getInput();
        $isTsumo = $input->isTsumo();
        $item = $input->getItem($seatWind);
        if ($item->isWinner()) {
            $winnerItem = $item;
            return $winnerItem->getPointTableItem()
                ->getWinnerPointChange($isTsumo, $winnerItem->isDealer());
        } elseif ($item->isLoser()) {
            $loserItem = $item;
            $selector = function (WinResultInputItem $winnerItem) use ($isTsumo, $loserItem) {
                return $winnerItem->getPointTableItem()
                    ->getLoserPointChange($isTsumo, $winnerItem->isDealer(), $loserItem->isDealer());
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
    function getReachChange(SeatWind $seatWind) {
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
    function getSeatChange(SeatWind $seatWind) {
        // total = seatWindTurn * 300
        // winner: total
        // loser: tsumo ? - total / loserCount : total * winnerCount
        // irrelevant: 0
        $input = $this->getInput();
        $item = $input->getItem($seatWind);
        $total = $input->getSeatWindTurn() * 300; // always dividable by 1/2/3
        if ($item->isWinner()) {
            return intval($total);
        } elseif ($item->isLoser()) {
            return $input->isTsumo()
                ? -intval($total / $input->getLoserCount())
                : -intval($total * $input->getWinnerCount());
        } else {
            return 0;
        }
    }
}