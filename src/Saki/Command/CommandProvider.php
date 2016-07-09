<?php
namespace Saki\Command;

use Saki\Command\Debug\InitCommand;
use Saki\Command\Debug\PassAllCommand;
use Saki\Command\Debug\ToNextRoundCommand;
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

        // debug only todo replace by pass
        $isPublicActor = $round->getArea($actor)->isPublicActor();
        if ($isPublicActor) {
            $allExecutableList->insertLast(new PassAllCommand($this->getRound()));
        }

        // debug only todo remove
        $toNextRoundCommand = new ToNextRoundCommand($this->getRound());
        if ($toNextRoundCommand->executable()) {
            $allExecutableList->insertLast($toNextRoundCommand);
        }

        // debug only todo remove
        if ($round->isGameOver()) {
            $initCommand = new InitCommand($round);
            $allExecutableList->insertLast($initCommand);
        }

        return $allExecutableList;
    }

    /**
     * @return ArrayList ArrayList of PlayerCommand.
     */
    function getAllExecutableList() {
        $round = $this->getRound();
        $actorList = $round->getGameData()->getPlayerType()
            ->getSeatWindList();
        $allExecutableList = (new ArrayList())
            ->fromSelectMany($actorList, [$this, 'getExecutableList']);
        return $allExecutableList;
    }
}