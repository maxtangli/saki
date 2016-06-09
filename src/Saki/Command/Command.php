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
use Saki\Game\Round;

/**
 * goal
 * - separate command logic into classes.
 * - provide string-style-command to support tests, remote transfer, replay, etc.
 * @package Saki\Command
 */
abstract class Command {
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
     * @return bool
     */
    static function isDebug() {
        return strpos(get_called_class(), 'Debug') !== false;
    }

    /**
     * Used in: PublicCommand.matchPhase()
     * @return bool
     */
    static function isRon() {
        return strpos(get_called_class(), 'Ron') !== false;
    }

    //region subuse Saki\Game\Round; class hooks
    /**
     * @return ParamDeclaration[]
     */
    static function getParamDeclarations() {
        // since abstract static function not allowed
        throw new \BadMethodCallException('Unimplemented static::getParamDeclarations().');
    }

    //endregion

    private $round;
    private $params = [];

    /**
     * @param Round $round
     * @param array $params
     */
    function __construct(Round $round, array $params = []) {
        $this->round = $round;
        $this->params = $params;
    }

    /**
     * @return string
     */
    function __toString() {
        $paramStrings = array_map(function ($param) {
            return (string)$param;
        }, $this->params);
        $tokens = array_merge([static::getName()], $paramStrings);
        return implode(' ', $tokens);
    }

    /**
     * @return Round
     */
    function getRound() {
        return $this->round;
    }

    /**
     * @param int $i
     * @return mixed
     */
    protected function getParam(int $i) {
        if (!(array_key_exists($i, $this->params))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $i[%s] for $this->params[%s]', $i, var_export($this->params, true))
            );
        }
        return $this->params[$i];
    }

    /**
     * @return bool
     */
    function executable() {
        return $this->executableImpl($this->getRound()) === true;
    }

    /**
     * @throws InvalidCommandException
     */
    function execute() {
        $executable = $this->executableImpl($this->getRound());
        if ($executable !== true) {
            $e = $executable instanceof \Exception ? $executable : new InvalidCommandException(
                sprintf('Bad method call of [%s()] on not executable command[%s].'
                    , __FUNCTION__, $this->__toString())
            );
            throw $e;
        }

        $this->executeImpl($this->getRound());
    }

    //region subuse Saki\Game\Round; class hooks
    /**
     * @param Round $round
     * @return bool
     * 
     */
    abstract protected function executableImpl(Round $round);

    /**
     * @param Round $round
     * @return
     * 
     */
    abstract protected function executeImpl(Round $round);
    //endregion
}

