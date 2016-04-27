<?php
/*
Command
- Command.new(context, specificParams...), Command.execute().
- goal: support central control on commands, such as history, priority,
- Usage: new DiscardCommand($context, $player, $tile).execute();

CommandParser
- goal: support string2command
- CommandParser.parse(string): first token as class, remains as params.
- Command.fromString(string): multi-poly impl of parse(string).
- Usage: CommandParser.parse('discard E 1p'), DiscardCommand.fromString('discard E 1p');

Command.executable() execute()
- executable: client's responsibility.
- execution
 */
namespace Saki\Command;

use Saki\Command\ParamDeclaration\ParamDeclaration;
use Saki\Util\Utils;

/**
 * goal
 * - separate command logic into classes.
 * - provide string-style-command to support tests, term, replay.
 *
 * @package Saki\Command
 */
abstract class Command {
    //region parser
    /**
     * @return string
     */
    static function getName() {
        // Saki\Command\DiscardCommand -> discard
        $cls = get_called_class();
        $s = substr($cls, strrpos($cls, '\\') + 1);
        $s = str_replace('Command', '', $s);
        $s = lcfirst($s);
        return $s;
    }

    /**
     * @param string $line
     * @return string
     */
    static function parseName(string $line) {
        $tokens = Utils::explodeSafe(' ', $line);
        if (empty($tokens)) {
            throw new \InvalidArgumentException();
        }
        $token = $tokens[0];
        $name = lcfirst($token);
        return $name;
    }

    /**
     * @param CommandContext $context
     * @param string $line
     * @return static
     */
    static function fromString(CommandContext $context, string $line) {
        $name = static::parseName($line);
        if ($name !== static::getName()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $name[%s] of $line[%s] for static::getName()[%s]', $name, $line, static::getName())
            );
        }

        // 'Discard E 1m' => ['E','1m']
        $paramStrings = Utils::explodeSafe(' ', $line);
        array_shift($paramStrings);

        $paramDeclarations = static::getParamDeclarations();
        $validCount = count($paramStrings) == count($paramDeclarations);
        if (!$validCount) {
            throw new \InvalidArgumentException(
                sprintf('Invalid param count, expect[%s] in command[%s] but given[%s] in line[%s].',
                    count($paramDeclarations), static::getName(), count($paramStrings), $line)
            );
        }

        // ['E','1m'] => [Tile, Tile] which is indeed constructor-required-params
        $objects = [$context];
        foreach ($paramDeclarations as $i => $paramDeclaration) {
            $paramString = $paramStrings[$i];
            /** @var ParamDeclaration $param */
            $param = new $paramDeclaration($context, $paramString);
            $obj = $param->toObject();
            $objects [] = $obj;
        }

        return new static(...$objects);
    }

    /**
     * @return string
     */
    function __toString() {
        $tokens = array_map(function ($param) {
            return is_object($param) ? $param->__toString() : $param;
        }, $this->getParams());
        array_unshift($tokens, static::getName());
        return implode(' ', $tokens);
    }

    //endregion

    /**
     * @return bool
     */
    static function isDebug() {
        $cls = get_called_class();
        $hasDebugToken = strpos($cls, 'Debug') !== false;
        return $hasDebugToken;
    }

    /**
     * Used in: PublicCommand.matchPhase()
     * @return bool
     */
    static function isRon() {
        $cls = get_called_class();
        $hasRonToken = strpos($cls, 'Ron') !== false;
        return $hasRonToken;
    }

    static function getParamDeclarations() {
        // since abstract static function not allowed
        throw new \BadMethodCallException('Unimplemented static::getParamDeclarations().');
    }

    private $context; // todo to be or not to be?
    private $params = [];

    function __construct(CommandContext $context, array $params = []) {
        $this->context = $context;
        $this->params = $params;
    }

    function getContext() {
        return $this->context;
    }

    protected function getParams() {
        return $this->params;
    }

    protected function getParam(int $i) {
        $valid = array_key_exists($i, $this->params);
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $i[%s] for $this->params[%s]', $i, var_export($this->params, true))
            );
        }
        return $this->params[$i];
    }

    function executable() {
        return $this->executableImpl($this->getContext());
    }

    abstract protected function executableImpl(CommandContext $context);

    function execute() {
        if (!$this->executable()) {
            throw new \BadMethodCallException(
                sprintf('Bad method call of [%s()] on not executable command[%s].'
                    , __FUNCTION__, $this->__toString())
            );
        }
        $this->executeImpl($this->getContext());
    }

    abstract protected function executeImpl(CommandContext $context);
}