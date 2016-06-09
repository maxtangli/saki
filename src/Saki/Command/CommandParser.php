<?php
namespace Saki\Command;

use Saki\Command\ParamDeclaration\ParamDeclaration;
use Saki\Util\Utils;

/**
 * @package Saki\Command
 */
class CommandParser {
    private $context;
    private $nameToClassMap;

    /**
     * @param CommandContext $context
     * @param CommandSet $commandSet
     */
    function __construct(CommandContext $context, CommandSet $commandSet) {
        $this->context = $context;

        // note: validation ignored
        $names = $commandSet->toArray(function ($class) {
            return $class::getName();
        });
        $classes = $commandSet->toArray();
        $this->nameToClassMap = array_combine($names, $classes);
    }

    /**
     * @return CommandContext
     */
    function getContext() {
        return $this->context;
    }

    /**
     * @return array
     */
    function getNameToClassMap() {
        return $this->nameToClassMap;
    }

    /**
     * @param string $name
     * @return class
     */
    function nameToClass(string $name) {
        $commands = $this->getNameToClassMap();
        if (!(array_key_exists($name, $commands))) {
            throw new \InvalidArgumentException(
                sprintf('command name[%s] not exist, forgot to pass it into parser?', $name)
            );
        }
        return $commands[$name];
    }

    /**
     * @param string $script
     * @return Command[]
     */
    function parseScript(string $script) {
        $lines = preg_split('/; /', $script);
        $commands = array_map(function ($line) {
            return $this->parseLine($line);
        }, $lines);
        return $commands;
    }

    /**
     * @param string $line
     * @return Command
     */
    function parseLine(string $line) {
        $tokens = Utils::explodeNotEmpty(' ', $line); // 'discard E 1m' => ['discard', 'E','1m']

        $name = lcfirst($tokens[0]); // 'Discard'
        $class = $this->nameToClass($name); // 'Saki\Command\PrivateCommand\DiscardCommand'

        $context = $this->getContext();
        $paramDeclarations = $class::getParamDeclarations(); // [SeatWindParam, TileParam]
        $paramStrings = array_slice($tokens, 1); // ['E','1m']
        $paramObjects = ParamDeclaration::toObjects($paramDeclarations, $paramStrings); // [SeatWind, TileParam]

        return new $class($context, ...$paramObjects);
    }
}