<?php
namespace Saki\Command;

use Saki\Command\ParamDeclaration\ParamDeclaration;
use Saki\Game\Round;
use Saki\Util\Utils;

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
        return lcfirst(Utils::strLastPart(get_called_class(), 'Command'));
    }

    /**
     * @return bool
     */
    static function isDebug() {
        return strpos(get_called_class(), 'Debug') !== false;
    }

    //region subclass hooks
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
            $e = $executable instanceof \Exception
                ? $executable
                : new InvalidCommandException($this->__toString(), 'not executable');
            throw $e;
        }

        $this->executeImpl($this->getRound());
    }

    //region subclass hooks
    /**
     * @param Round $round
     * @return bool|InvalidCommandException
     */
    abstract protected function executableImpl(Round $round);

    /**
     * @param Round $round
     * @return
     */
    abstract protected function executeImpl(Round $round);
    //endregion
}

