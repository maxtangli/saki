<?php
namespace Saki\Game;

use Saki\Game\Tile\TileSet;
use Saki\Game\Wall\DeadWall;
use Saki\Game\Wall\DrawWall;
use Saki\Game\Wall\Stack;
use Saki\Game\Wall\StackList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Game
 */
class Wall {
    // immutable
    private $tileSet;
    private $playerType;
    private $dicePair;
    // variable
    private $stackList;
    private $drawWall;
    private $deadWall;
    private $doraFacade;

    /**
     * @param TileSet $tileSet
     * @param PlayerType $playerType
     */
    function __construct(TileSet $tileSet, PlayerType $playerType) {
        $valid = ($tileSet->count() == 136);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $this->tileSet = $tileSet;
        $this->dicePair = new DicePair();
        $this->playerType = $playerType;
        $generateStack = function () {
            return new Stack();
        };
        $this->stackList = (new StackList())->fromGenerator(4 * 17, $generateStack);
        $this->drawWall = new DrawWall();

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
        list($drawStackList, $deadStackList) = $this->stackList->toTwoBreak($diceResult);
        $this->drawWall->init($drawStackList);
        $this->deadWall = new DeadWall($deadStackList);
        $this->doraFacade = new DoraFacade($this->deadWall);

        // 5.The deal
        // already done in drawWall

        // 6.Open dora indicator
        // already done in deadWall
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getDeadWall()->__toString() . ',' . $this->getDrawWall()->__toString();
    }

    /**
     * @return array
     */
    function toJson() {
        $a = $this->getDeadWall()->toJson();
        $a['remainTileCount'] = $this->getDrawWall()->getRemainTileCount();
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
     * @param SeatWind $seatWind
     * @return StackList
     */
    function getActorWall(SeatWind $seatWind) {
        $n = 17;
        $start = ($seatWind->getIndex() - 1) * $n;
        return $this->stackList->getCopy()
            ->take($start, $n);
    }

    /**
     * @return DrawWall
     */
    function getDrawWall() {
        return $this->drawWall;
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