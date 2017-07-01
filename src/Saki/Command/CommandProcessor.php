<?php

namespace Saki\Command;

use Saki\Command\DebugCommand\DebugCommand;
use Saki\Game\Round;
use Saki\Game\SeatWind;

/**
 * @package Saki\Command
 */
class CommandProcessor {
    private $parser;
    private $provider;

    /**
     * @param Round $round
     * @param CommandSet $commandSet
     */
    function __construct(Round $round, CommandSet $commandSet) {
        $this->parser = new CommandParser($round, $commandSet);
        $this->provider = new CommandProvider($round, $commandSet);
    }

    function init() {
        $this->getProvider()->clearProvideAll();
    }

    /**
     * @return CommandParser
     */
    function getParser() {
        return $this->parser;
    }

    /**
     * @return CommandProvider
     */
    function getProvider() {
        return $this->provider;
    }

    /**
     * To support replay, introduce CommandInvoker class.
     * @param string[] ...$scripts
     */
    function process(... $scripts) {
        $script = implode('; ', $scripts);
        $commands = $this->getParser()->parseScript($script);
        array_walk($commands, function (Command $command) {
            // to ensure isSkipTrivialPass handler access latest provider data
            $this->getProvider()->clearProvideAll();

            $command->execute();
        });
    }

    /**
     * @param string $scriptLine
     * @param SeatWind|null $requireActor
     * @param bool $allowDebugCommand
     */
    function processLine(string $scriptLine, SeatWind $requireActor = null, $allowDebugCommand = true) {
        $command = $this->getParser()->parseLine($scriptLine);

        if ($command instanceof DebugCommand) {
            if (!$allowDebugCommand) {
                throw new \InvalidArgumentException(
                    "Invalid command: DebugCommand[$command] is not allowed."
                );
            }
        } elseif ($command instanceof PlayerCommand) {
            $validActor = !isset($requireActor) || $command->getActor()->isSame($requireActor);
            if (!$validActor) {
                throw new \InvalidArgumentException(
                    "Invalid command: [$command] do not match actor [$requireActor]."
                );
            }
        } else {
            throw new \LogicException();
        }

        $this->process($command->__toString());
    }
}