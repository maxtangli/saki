<?php
namespace Saki\Win\Result;

use Saki\Game\PlayerType;
use Saki\Game\SeatWind;
use Saki\Util\Immutable;

/**
 * @package Saki\Win\Result
 */
abstract class Result implements Immutable {
    private $playerType;
    private $resultType;

    /**
     * @param PlayerType $playerType
     * @param ResultType $resultResultType
     */
    function __construct(PlayerType $playerType, ResultType $resultResultType) {
        $this->playerType = $playerType;
        $this->resultType = $resultResultType;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getResultType()->__toString();
    }

    /**
     * @return PlayerType
     */
    function getPlayerType() {
        return $this->playerType;
    }

    /**
     * @return ResultType
     */
    function getResultType() {
        return $this->resultType;
    }

    /**
     * @return array An array to indicate point changes in format e.x. ['E' => -1000 ...].
     */
    function getPointChangeMap() {
        return $this->getPlayerType()->getSeatWindMap([$this, 'getPointChange']);
    }

    //region subclass hooks
    /**
     * @return bool
     */
    abstract function isKeepDealer();

    /**
     * @param SeatWind $seatWind
     * @return int
     */
    abstract function getPointChange(SeatWind $seatWind);
    //endregion
}