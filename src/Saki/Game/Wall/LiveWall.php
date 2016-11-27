<?php
namespace Saki\Game\Wall;

use Saki\Game\Tile\Tile;

/**
 * @package Saki\Game\Wall
 */
abstract class LiveWall {
    private $fromLast;
    /** @var StackList */
    private $stackList;

    protected function __construct(bool $fromLast) {
        $this->fromLast = $fromLast;
        $this->init();
    }

    /**
     * @param StackList|null $stackList
     */
    function init(StackList $stackList = null) {
        $actualStackList = $stackList ?? new StackList();
        $notEmpty = function (Stack $stack) {
            return !$stack->isEmpty();
        };
        if (!$actualStackList->all($notEmpty)) {
            throw new \InvalidArgumentException();
        }

        $this->stackList = $actualStackList;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->stackList->__toString();
    }

    /**
     * @param Tile $tile
     */
    function debugSetNextTile(Tile $tile) {
        $this->getCurrentStack()->setNextPopTile($tile); // validate
    }

    /**
     * @param int $n
     */
    function debugSetRemainTileCount(int $n) {
        $nTodo = $this->getRemainTileCount() - $n;

        $valid = ($nTodo >= 0);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        while ($nTodo-- > 0) {
            $this->outNext();
        }
    }

    /**
     * @return Stack
     */
    private function getCurrentStack() {
        return $this->fromLast
            ? $this->stackList->getLast()
            : $this->stackList->getFirst(); // validate
    }

    protected function ableOutNext() {
        return $this->getRemainTileCount() > 0;
    }

    /**
     * @return Tile
     */
    protected function outNext() {
        $currentStack = $this->getCurrentStack(); // validate
        $tile = $currentStack->popTile();

        if ($currentStack->isEmpty()) {
            $index = $this->fromLast ? $this->stackList->getLastIndex() : $this->stackList->getFirstIndex();
            $this->stackList->removeAt($index);
        }

        return $tile;
    }

    /**
     * @return int
     */
    function getRemainStackCount() {
        return $this->stackList->count();
    }

    /**
     * @return int
     */
    function getRemainTileCount() {
        $stackCount = $this->getRemainStackCount();
        return $stackCount
            ? ($stackCount - 1) * 2 + $this->getCurrentStack()->getCount()
            : 0;
    }
}