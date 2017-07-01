<?php
namespace Saki\Command;

use React\EventLoop\Timer\Timer;
use Saki\Command\DebugCommand\InitCommand;
use Saki\Command\DebugCommand\ToNextRoundCommand;
use Saki\Command\PrivateCommand\RiichiCommand;
use Saki\Command\PublicCommand\PassCommand;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Util\MsTimer;

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

    private $provideAllBuffer = null; // a temp solution to speed up

    function clearProvideAll() {
        $this->provideAllBuffer = null;
    }

    /**
     * @return CommandProvided
     */
    function provideAll() {
        if (!isset($this->provideAllBuffer)) {
            $provideActorAll = function (SeatWind $actor) {
                return $this->provideActorAll($actor);
            };
            $round = $this->getRound();
            $actorCommands = $round->getRule()->getPlayerType()
                ->getSeatWindMap($provideActorAll);
            $this->provideAllBuffer = new CommandProvided($actorCommands);
        }
        return $this->provideAllBuffer;
    }

    /**
     * @param SeatWind $actor
     * @return ArrayList ArrayList of PlayerCommand.
     */
    function provideActorAll(SeatWind $actor) {
        $round = $this->getRound();
        $providerActorCommand = function (string $class) use ($actor) {
//            $ms = MsTimer::create()->measure(function () use($class, $actor) {
//                $this->provideActorCommand($class, $actor);
//            });
//            echo "$class: $ms ms\n";
            return $this->provideActorCommand($class, $actor);
        };
        $allExecutableList = (new ArrayList())
            ->fromSelectMany($this->getPlayerCommandSet(), $providerActorCommand);

        // debug only todo remove
        $toNextRoundCommand = new ToNextRoundCommand($this->getRound());
        if ($toNextRoundCommand->executable()) {
            $allExecutableList->insertLast($toNextRoundCommand);
        }

        // debug only todo remove
        if ($round->getPhaseState()->isGameOver()) {
            $initCommand = new InitCommand($round);
            $allExecutableList->insertLast($initCommand);
        }

        return $allExecutableList;
    }

    /**
     * @param string $class
     * @param $actor
     * @return ArrayList ArrayList of PlayerCommand.
     */
    private function provideActorCommand(string $class, $actor) {
        $round = $this->getRound();
        if (!$class::matchPhaseAndActor($round, $actor)) {
            return new ArrayList();
        }

        $otherParamsList = $class::getOtherParamsListRaw($round, $actor, $round->getArea($actor));
        return $this->createMany($class, $round, $actor, $otherParamsList);
    }

    /**
     * @param string $class
     * @param Round $round
     * @param SeatWind $actor
     * @param ArrayList $otherParamsListRaw
     * @return ArrayList
     */
    private function createMany(string $class, Round $round, SeatWind $actor,
                                ArrayList $otherParamsListRaw) {
        $toCommand = function ($otherParams) use ($class, $round, $actor) {
            $actualParams = is_array($otherParams) ? $otherParams : [$otherParams];
            array_unshift($actualParams, $actor);
            return new $class($round, $actualParams);
        };
        $executable = function (Command $command) {
            return $command->executable();
        };
        return $otherParamsListRaw
            ->toArrayList($toCommand)
            ->where($executable);
    }
}