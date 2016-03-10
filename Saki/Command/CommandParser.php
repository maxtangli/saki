<?php
namespace Saki\Command;

class CommandParser {
    private $context;
    private $classes;

    function __construct(CommandContext $context, array $classes) {
        $this->context = $context;
        $this->classes = array_combine(array_map(function ($class) {
            return $class::getName();
        }, $classes), $classes);  // note: validation ignored
    }

    function getContext() {
        return $this->context;
    }

    function getClasses() {
        return $this->classes;
    }

    function getClass(string $name) {
        $commands = $this->getClasses();
        $valid = array_key_exists($name, $commands);
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf('command name[%s] not exist, forgot to pass it into parser?', $name)
            );
        }
        $class = $commands[$name];
        return $class;
    }

    /**
     * @param string $line
     * @return Command
     */
    function parseLine(string $line) {
        $name = Command::parseName($line);
        $class = $this->getClass($name);
        $command = $class::fromString($this->getContext(), $line);
        return $command;
    }

    /**
     * @param string e.x. 'discard E 1m; pass'
     * @return Command[]
     */
    function parseScript(string $script) {
        $lines = preg_split('/; /', $script);
        $commands = array_map(function (string $line) {
            return $this->parseLine($line);
        }, $lines);
        return $commands;
    }
}

