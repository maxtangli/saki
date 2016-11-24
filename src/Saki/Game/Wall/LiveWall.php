<?php
namespace Saki\Game\Wall;

use Saki\Game\PlayerType;
use Saki\Game\Tile\Tile;

/**
 * @package Saki\Game\Wall
 */
class LiveWall {
    /** @var StackList */
    private $stackList;

    /**
     * @param StackList|null $stackList
     */
    function __construct(StackList $stackList = null) {
        $this->init($stackList ?? new StackList());
    }

    /**
     * @param StackList $stackList
     */
    function init(StackList $stackList) {
        $notEmpty = function (Stack $stack) {
            return !$stack->isEmpty();
        };
        if (!$stackList->all($notEmpty)) {
            throw new \InvalidArgumentException();
        }

        $this->stackList = $stackList;
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
    function debugSetNextDrawTile(Tile $tile) {
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
            $this->draw();
        }
    }

    /**
     * @return Stack
     */
    private function getCurrentStack() {
        return $this->stackList->getLast(); // validate
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

    /**
     * @return bool
     */
    function isBottomOfTheSea() {
        return $this->getRemainTileCount() == 0;
    }

    /**
     * @return Tile
     */
    function draw() {
        $currentStack = $this->getCurrentStack();
        $tile = $currentStack->popTile();
        if ($currentStack->isEmpty()) {
            $this->stackList->removeLast();
        }
        return $tile;
    }

    /**
     * @param PlayerType $playerType
     * @return Tile[][] e.x. [E => [1s,2s...] ...]
     */
    function deal(PlayerType $playerType) {
        $result = $playerType->getSeatWindMap([]);
        foreach ([4, 4, 4, 1] as $drawTileCount) {
            foreach ($result as $k => $notUsed) {
                $nTodo = $drawTileCount;
                while ($nTodo-- > 0) {
                    $result[$k][] = $this->draw();
                }
            }
        }
        return $result;
    }
}