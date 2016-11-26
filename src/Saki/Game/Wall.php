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
    private $playerType;
    private $dicePair;
    // variable
    private $stackList;
    private $liveWall;
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
        // already done in liveWall

        // 6.Open dora indicator
        // already done in deadWall
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
        $a['actorWalls'] = $this->toActorWallsJson();
        return $a;
    }

    /**
     * @return array e.x
     */
    private function toActorWallsJson() {
        $keySelector = function (SeatWind $seatWind) {
            return $seatWind->__toString();
        };
        $valueSelector = function (SeatWind $seatWind) {
            return $this->getActorWall($seatWind)->toJson();
        };
        return $this->playerType->getSeatWindList()
            ->toMap($keySelector, $valueSelector);
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
        $start = $seatWind->getIndex();
        return $this->stackList->getCopy()
            ->take($start, 136 / 4);
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