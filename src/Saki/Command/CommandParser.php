<?php
namespace Saki\Command;

use Saki\Command\ParamDeclaration\ParamDeclaration;
use Saki\Game\Round;
use Saki\Util\Utils;

/**
 * @package Saki\Command
 */
class CommandParser {
    private $round;
    private $nameToClassMap;

    /**
     * @param Round $round
     * @param CommandSet $commandSet
     */
    function __construct(Round $round, CommandSet $commandSet) {
        $this->round = $round;

        // note: validation ignored
        $names = $commandSet->toArray(function ($class) {
            return $class::getName();
        });
        $classes = $commandSet->toArray();
        $this->nameToClassMap = array_combine($names, $classes);
    }

    /**
     * @return Round
     */
    function getRound() {
        return $this->round;
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

        $round = $this->getRound();
        $paramDeclarations = $class::getParamDeclarations(); // [SeatWindParam, TileParam]
        $paramStrings = array_slice($tokens, 1); // ['E','1m']
        $paramObjects = ParamDeclaration::toObjects($paramDeclarations, $paramStrings); // [SeatWind, TileParam]

        return new $class($round, ...$paramObjects);
    }
}