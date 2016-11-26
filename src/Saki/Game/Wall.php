<?php
namespace Saki\Game;

use Saki\Game\Tile\TileSet;
use Saki\Game\Wall\DeadWall;
use Saki\Game\Wall\LiveWall;
use Saki\Game\Wall\Stack;
use Saki\Game\Wall\StackList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Game
 */
class Wall {
    // immutable
    private $tileSet;
    private $dicePair;
    // variable
    private $stackList;
    private $liveWall;
    private $deadWall;
    private $doraFacade;

    /**
     * @param TileSet $tileSet
     */
    function __construct(TileSet $tileSet) {
        $valid = ($tileSet->count() == 136);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $this->tileSet = $tileSet;
        $this->dicePair = new DicePair();
        $generateStack = function () {
            return new Stack();
        };
        $this->stackList = (new StackList())->fromGenerator(4 * 17, $generateStack);
        $this->liveWall = new LiveWall();

        $this->init();
    }

    function init() {
        // clear
        $initStack = function (Stack $stack) {
            $stack->init();
        };
        $this->stackList->walk($initStack);

        // 1.Mix the tiles
        $tileList = $this->tileSet->toTileList()->shuffle();

        // 2.Building the wall
        $chunkList = new ArrayList($tileList->toChunks(2));
        $setChunk = function (Stack $stack, array $chunk) {
            $stack->setTileChunk($chunk);
            return $stack;
        };
        $this->stackList->fromMapping($this->stackList, $chunkList, $setChunk);

        // 3.Roll two dice
        $diceResult = $this->getDicePair()->roll();

        // 4.Break the wall
        list($liveStackList, $deadStackList) = $this->stackList->toTwoBreak($diceResult);
        $this->liveWall->init($liveStackList);
        $this->deadWall = new DeadWall($deadStackList->toTileList());
        $this->doraFacade = new DoraFacade($this->deadWall);

        // 5.The deal
        // todo

        // 6.Open dora indicator
        // todo
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getDeadWall()->__toString() . ',' . $this->getLiveWall()->__toString();
    }

    /**
     * @return array
     */
    function toJson() {
        $a = $this->getDeadWall()->toJson();
        $a['remainTileCount'] = $this->getLiveWall()->getRemainTileCount();
        return $a;
    }

    /**
     * @return TileSet
     */
    function getTileSet() {
        return $this->tileSet;
    }

    /**
     * @return DicePair
     */
    function getDicePair() {
        return $this->dicePair;
    }

    /**
     * @return LiveWall
     */
    function getLiveWall() {
        return $this->liveWall;
    }

    /**
     * @return DeadWall
     */
    function getDeadWall() {
        return $this->deadWall;
    }

    /**
     * @return DoraFacade
     */
    function getDoraFacade() {
        return $this->doraFacade;
    }
}