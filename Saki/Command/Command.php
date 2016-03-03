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

Phase System
- todo

Command.getExecutables()
- todo goal: list all possible commands for a given player
 */
namespace Saki\Command;

use Saki\Command\ParamDeclaration\ParamDeclaration;
use Saki\Util\Utils;

abstract class Command {
    //region parser
    static function parseName(string $line) {
        $tokens = Utils::explodeSafe(' ', $line);
        if (empty($tokens)) {
            throw new \InvalidArgumentException();
        }
        $token = $tokens[0];
        $name = lcfirst($token);
        return $name;
    }

    static function fromString(CommandContext $context, string $line) {
        $name = static::parseName($line);
        if ($name !== static::getName()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $name[%s] of $line[%s] for static::getName()[%s]', $name, $line, static::getName())
            );
        }

        $strings = Utils::explodeSafe(' ', $line, 1);
        array_shift($strings);

        $declarations = static::getParamDeclarations();
        $validCount = count($strings) == count($declarations);
        if (!$validCount) {
            throw new \InvalidArgumentException();
        }

        $objects = [$context];
        foreach ($declarations as $i => $paramDeclaration) {
            $string = $strings[$i];
            /** @var ParamDeclaration $param */
            $param = new $paramDeclaration($context, $string);
            $obj = $param->toObject();
            $objects [] = $obj;
        }

        return new static(...$objects);
    }

    static function getName() {
        // Saki\Command\DiscardCommand -> discard
        $cls = get_called_class();
        $s = substr($cls, strrpos($cls, '\\') + 1);
        $s = str_replace('Command', '', $s);
        $s = lcfirst($s);
        return $s;
    }

    function __toString() {
        $tokens = array_map(function ($param) {
            return is_object($param) ? $param->__toString() : $param;
        }, $this->getParams()); // todo right?
        array_unshift($tokens, static::getName());
        return implode(' ', $tokens);
    }

    //endregion

    static function getParamDeclarations() {
        throw new \BadMethodCallException('Forgot to implemented.');
    }

    private $context;
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

    abstract function executable();

    function execute() {
        if (!$this->executable()) {
            throw new \InvalidArgumentException('Invalid command');
        }
        $this->executeImpl();
    }

    abstract function executeImpl();
}