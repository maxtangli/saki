<?php
namespace Saki\Win\Result;

use Saki\Game\PlayerType;
use Saki\Game\SeatWind;

/**
 * @package Saki\Win\Result
 */
class AbortiveDrawResult extends Result {
    /**
     * @param PlayerType $playerType
     * @param ResultType $drawResultType
     */
    function __construct(PlayerType $playerType, ResultType $drawResultType) {
        if (!$drawResultType->isAbortiveDraw()) {
            throw new \InvalidArgumentException();
        }
        parent::__construct($playerType, $drawResultType);
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