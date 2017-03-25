<?php

namespace Saki\Win\Result;

use Saki\Game\SeatWind;
use Saki\Util\ArrayList;

/**
 * @package Saki\Win\Result
 */
class WinResult extends Result {
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
    private function getInput() {
        return $this->input;
    }

    /**
     * @return ArrayList
     */
    function getWinReportList() {
        return $this->getInput()->getWinReportList();
    }

    //region impl

    function isKeepDealer() {
        // Dealer is winner
        return $this->getInput()->getItem(SeatWind::createEast())
            ->isWinner();
    }

    function getPointChange(SeatWind $seatWind) {
        return $this->getTableChange($seatWind)
            + $this->getRiichiChange($seatWind)
            + $this->getSeatChange($seatWind);
    }

    //endregion

    /**
     * @param SeatWind $seatWind
     * @return int
     */
    function getTableChange(SeatWind $seatWind) {
        $input = $this->getInput();
        $isTsumo = $input->isTsumo();
        $item = $input->getItem($seatWind);

        if ($item->isWinner()) {
            $winnerItem = $item;
            return $winnerItem->getPointTableItem()
                ->getWinnerPointChange($isTsumo, $winnerItem->isDealer());
        }

        $getPay = function (WinResultInputItem $winnerItem) use ($isTsumo, $item) {
            $paoRatio = $this->getInput()->getPaoRatioOrFalse($item, $winnerItem);
            if ($paoRatio !== false) {
                $winnerPoint = $winnerItem->getPointTableItem()
                    ->getWinnerPointChange($isTsumo, $winnerItem->isDealer());
                return intval(-$winnerPoint * $paoRatio);
            }

            if ($item->isLoser()) {
                $loserPointChange = $winnerItem->getPointTableItem()
                    ->getLoserPointChange($isTsumo, $winnerItem->isDealer(), $item->isDealer());
                return $loserPointChange;
            }

            return 0;
        };
        return $input->getWinnerItemList()->getSum($getPay);
    }

    /**
     * @param SeatWind $seatWind
     * @return int
     */
    function getRiichiChange(SeatWind $seatWind) {
        $input = $this->getInput();
        return $input->isNearestWinner($seatWind)
            ? $input->getRiichiPoints()
            : 0;
    }

    /**
     * @param SeatWind $seatWind
     * @return int
     */
    function getSeatChange(SeatWind $seatWind) {
        $input = $this->getInput();
        $item = $input->getItem($seatWind);
        $perWinnerTotal = $input->getSeatWindTurn() * 300; // always dividable by 1/2/3

        if ($item->isWinner()) {
            return intval($perWinnerTotal);
        }

        $getPay = function (WinResultInputItem $winnerItem) use ($item, $perWinnerTotal) {
            $input = $this->getInput();

            $paoRatio = $input->getPaoRatioOrFalse($item, $winnerItem);
            if ($paoRatio !== false) {
                return -$perWinnerTotal * $paoRatio;
            }

            if ($item->isLoser()) {
                return $input->isTsumo()
                    ? -intval($perWinnerTotal / $input->getLoserCount())
                    : -intval($perWinnerTotal);
            }

            return 0;
        };
        return $this->getInput()->getWinnerItemList()->getSum($getPay);
    }
}