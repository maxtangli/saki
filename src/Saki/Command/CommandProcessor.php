<?php
namespace Saki\Command;

/**
 * @package Saki\Command
 */

class CommandProcessor {
    private $parser;

    /**
     * @param CommandParser $parser
     */
    function __construct(CommandParser $parser) {
        $this->parser = $parser;
    }

    /**
     * @return CommandParser
     */
    function getParser() {
        return $this->parser;
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