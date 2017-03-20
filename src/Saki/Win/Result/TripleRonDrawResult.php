<?php
namespace Saki\Win\Result;

use Saki\Game\PlayerType;
use Saki\Game\SeatWind;

/**
 * @package Saki\Win\Result
 */
class TripleRonDrawResult extends Result {
    private $winners;
    /**
     * @param PlayerType $playerType
     * @param SeatWind[] $winners
     */
    function __construct(PlayerType $playerType, array $winners) {
        parent::__construct($playerType, ResultType::create(ResultType::TRIPLE_RON_DRAW));
        $this->winners = $winners;
    }

    /**
     * @return SeatWind[]
     */
    function getWinners() {
        return $this->winners;
    }

    //region impl
    function isKeepDealer() {
        return true;
    }

    function getPointChange(SeatWind $seatWind) {
        return 0;
    }
    //endregion
}