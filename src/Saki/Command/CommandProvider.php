<?php
namespace Saki\Command;

use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command
 */
class CommandProvider {
    private $round;
    private $playerCommandSet;

    /**
     * @param Round $round
     * @param CommandSet $commandSet
     */
    function __construct(Round $round, CommandSet $commandSet) {
        $this->round = $round;
        $this->playerCommandSet = $commandSet->toPlayerCommandSet();
    }

    /**
     * @return Round
     */
    function getRound() {
        return $this->round;
    }

    /**
     * @return ArrayList
     */
    function getPlayerCommandSet() {
        return $this->playerCommandSet;
    }

    /**
     * @param SeatWind $actor
     * @return ArrayList ArrayList of PlayerCommand.
     */
    function getExecutableList(SeatWind $actor) {
        $round = $this->getRound();
        $getClassExecutableList = function (string $class) use ($round, $actor) {
            return $class::getExecutableList($round, $actor);
        };
        $allExecutableList = (new ArrayList())
            ->fromSelectMany($this->getPlayerCommandSet(), $getClassExecutableList);
        return $allExecutableList;
    }
}