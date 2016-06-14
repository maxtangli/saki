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
     * @return Command[]
     */
    function getExecutables(SeatWind $actor) {
        $round = $this->getRound();
        $getClassExecutables = function (string $class) use ($round, $actor) {
            return $class::getExecutables($round, $actor);
        };
        $executables = (new ArrayList())
            ->fromSelectMany($this->getPlayerCommandSet(), $getClassExecutables)
            ->toArray();
        return $executables;
    }
}