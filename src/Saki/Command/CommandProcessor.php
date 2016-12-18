<?php
namespace Saki\Command;

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
            $command->execute();
        });
        $this->getProvider()->clearProvideAll();
    }

    /**
     * @param string $scriptLine
     * @param SeatWind|null $requireActor
     * @throws \InvalidArgumentException
     */
    function processLine(string $scriptLine, SeatWind $requireActor = null) {
        $command = $this->getParser()->parseLine($scriptLine);
        if ($requireActor) {
            if (!$command instanceof PlayerCommand) {
                throw new \InvalidArgumentException('not PlayerCommand.');
            }

            if (!$command->getActor() == $requireActor) {
                throw new \InvalidArgumentException('not actor.');
            }
        }
        $this->process($command->__toString());
    }
}