<?php
namespace Saki\Game;

use Saki\Util\ArrayList;

/**
 * History of declarations include chow, pong, kong.
 * Note that current phase is not considered since it do not affects hasDeclare() result.
 * Used in: yaku analyze.
 * @package Saki\Game
 */
class DeclareHistory {

    /**
     * @var ArrayList an ArrayList with ascend declare RoundTurn values.
     */
    private $list;

    function __construct() {
        $this->list = new ArrayList();
    }

    function reset() {
        $this->list->removeAll();
    }

    /**
     * @param RoundTurn $roundTurn
     */
    function recordDeclare(RoundTurn $roundTurn) {
        $valid = $this->list->isEmpty() ||
            $roundTurn->isAfterOrSame($this->list->getLast());
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $this->list->insertLast($roundTurn);
    }

    /**
     * @param RoundTurn $fromRoundTurn
     * @return bool
     */
    function hasDeclare(RoundTurn $fromRoundTurn) {
        return $this->list->isEmpty() ?
            false :
            $fromRoundTurn->isBeforeOrSame($this->list->getLast());
    }
}