<?php
namespace Saki\Game;

use Saki\Util\ArrayList;

/**
 * History of chow, pong, kong declarations. Used in: yaku analyze.
 * Note: my most satisfied class!
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
            $roundTurn->isLaterThanOrSame($this->list->getLast());
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
            $fromRoundTurn->isEarlierThanOrSame($this->list->getLast());
    }
}