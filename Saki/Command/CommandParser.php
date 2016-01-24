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
            throw new \InvalidArgumentException();
        }
        $class = $commands[$name];
        return $class;
    }

    function parse(string $line) {
        $name = Command::parseName($line);
        $class = $this->getClass($name);
        return $class::fromString($this->getContext(), $line);
    }
}