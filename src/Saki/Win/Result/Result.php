<?php
namespace Saki\Win\Result;

use Saki\Game\PlayerType;
use Saki\Game\SeatWind;
use Saki\Util\Immutable;

/**
 * @package Saki\Win\Result
 */
abstract class Result implements Immutable {
    private $playerCount;
    private $resultType;

    /**
     * @param PlayerType $playerType
     * @param ResultType $resultResultType
     */
    function __construct(PlayerType $playerType, ResultType $resultResultType) {
        $this->playerCount = $playerType->getValue();
        $this->resultType = $resultResultType;
    }

    /**
     * @return ResultType
     */
    function __toString() {
        return $this->getResultType()->__toString();
    }

    /**
     * @return int
     */
    function getPlayerCount() {
        return $this->playerCount;
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
        $keyList = SeatWind::createList($this->getPlayerCount()); // todo refactor
        $valueList = $keyList->toArrayList(function (SeatWind $seatWind) {
            return $this->getPointChange($seatWind);
        });
        return array_combine($keyList->toArray(), $valueList->toArray());
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