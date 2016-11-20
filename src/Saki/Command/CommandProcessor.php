<?php
namespace Saki\Command;
use Saki\Game\Round;

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
     * @param string[] ...$scripts
     */
    function process(... $scripts) {
        $script = implode('; ', $scripts);
        $commands = $this->getParser()->parseScript($script);
        array_walk($commands, function (Command $command) {
            $command->execute();
        });
    }
}