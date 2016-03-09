<?php
namespace Saki\Command;

class CommandProcessor {
    private $parser;

    function __construct(CommandParser $parser) {
        $this->parser = $parser;
    }

    function getParser() {
        return $this->parser;
    }

    function process(string $script) {
        $commands = $this->getParser()->parseScript($script);
        foreach ($commands as $command) {
            $command->execute();
        }
    }
}