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
    private $provideCommandSet;

    /**
     * @param Round $round
     * @param CommandSet $commandSet
     */
    function __construct(Round $round, CommandSet $commandSet) {
        $this->round = $round;
        $this->provideCommandSet = $commandSet->toArrayList()->where(function (string $command) {
            return is_subclass_of($command, PlayerCommand::class)
            && !$command::isDebug();
        });
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
    function getProvideCommandSet() {
        return $this->provideCommandSet;
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
            ->fromSelectMany($this->getProvideCommandSet(), $getClassExecutables)
            ->toArray();
        return $executables;
    }
}