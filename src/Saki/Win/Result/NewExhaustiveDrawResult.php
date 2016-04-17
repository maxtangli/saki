<?php
namespace Saki\Win\Result;
use Saki\Game\PlayerType;
use Saki\Game\SeatWind;

/**
 * @package Saki\Win\Result
 */
class NewExhaustiveDrawResult extends NewResult {
    private $waitingMap;

    /**
     * @param array $waitingMap An array in format: ['E' => $isWaiting ...].
     */
    function __construct(array $waitingMap) {
        // todo validate
        $this->waitingMap = $waitingMap;
        $playerType = PlayerType::create(count($waitingMap));
        $resultType = ResultType::create(ResultType::EXHAUSTIVE_DRAW);
        parent::__construct($playerType, $resultType);
    }

    //region impl
    function isKeepDealer() {
        return $this->isDealerWaiting();
    }

    function getPointChange(SeatWind $seatWind) {
        list($waitingCount, $notWaitingCount) = $this->getCounts();
        
        if ($waitingCount == 0) {
            return 0;
        }

        // 3000 points from notWaiting players to waiting players
        // todo right for 2 or 3 players game?
        return $this->isWaiting($seatWind)
            ? 3000 / $notWaitingCount
            : -3000 / $waitingCount;
    }
    //endregion

    /**
     * @return bool
     */
    protected function isDealerWaiting() {
        return $this->isWaiting(SeatWind::createEast());
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    protected function isWaiting(SeatWind $seatWind) {
        return $this->waitingMap[$seatWind->__toString()];
    }

    /**
     * @return int[] Return an array in format: [$waitingCount, $notWaitingCount].
     */
    protected function getCounts() {
        $yes = $no = 0;
        foreach ($this->waitingMap as $v) {
            if ($v) {
                ++$yes;
            } else {
                ++$no;
            }
        }
        return [$yes, $no];
    }
}