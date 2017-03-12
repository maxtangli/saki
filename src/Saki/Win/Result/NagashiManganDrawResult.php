<?php

namespace Saki\Win\Result;

use Saki\Game\SeatWind;
use Saki\Win\Point\FanAndFu;
use Saki\Win\Point\PointTable;

class NagashiManganDrawResult extends Result {
    private $exhaustiveDrawResult;
    private $isNagashiManganMap;

    /**
     * @param ExhaustiveDrawResult $exhaustiveDrawResult
     * @param array $isNagashiManganMap e.g. ['E' => $isNagashiMangan, ...]
     */
    function __construct(ExhaustiveDrawResult $exhaustiveDrawResult, array $isNagashiManganMap) {
        parent::__construct(
            $exhaustiveDrawResult->getPlayerType(),
            ResultType::create(ResultType::NAGASHIMANGAN_DRAW)
        );
        $this->exhaustiveDrawResult = $exhaustiveDrawResult;
        $this->isNagashiManganMap = $isNagashiManganMap; // ignore validation
    }

    //region impl
    function isKeepDealer() {
        return $this->exhaustiveDrawResult->isKeepDealer();
    }

    function getPointChange(SeatWind $seatWind) {
        list($yesCount, $noCount) = $this->getCounts();
        if ($noCount == 0) {
            return 0;
        }

        // 複数の者が同時に流し満貫を完成させる場合、
        // 全員が満貫のツモ和了として完成させた者同士の点数を相殺する。
        // 例えば東家と西家が完成させた場合、東家が+8000点（12000点-4000点）、西家が+4000点（8000点-4000点）、南家と北家が-6000点（-4000点-2000点）となる。
        $pointItem = PointTable::create()->getPointItem(new FanAndFu(5, 0));
        $winnerPointChange = 8000;
        $loserPointChange = $pointItem->getLoserPointChange(true, false, $seatWind->isDealer());
        return $this->isNagashiMangan($seatWind)
            ? $winnerPointChange + ($yesCount - 1) * $loserPointChange
            : $yesCount * $loserPointChange;
    }
    //endregion

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    private function isNagashiMangan(SeatWind $seatWind) {
        return $this->isNagashiManganMap[$seatWind->__toString()];
    }

    /**
     * @return int[] Return an array in format: [$waitingCount, $notWaitingCount].
     */
    private function getCounts() {
        $yes = $no = 0;
        foreach ($this->isNagashiManganMap as $v) {
            if ($v) {
                ++$yes;
            } else {
                ++$no;
            }
        }
        return [$yes, $no];
    }
}