<?php
namespace Saki\Game;

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Game\Tile\TileSet;
use Saki\Game\Wall\IndicatorWall;
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
    // variable
    private $initialTileList;
    private $dicePair;
    private $stackList;
    private $drawWall;
    private $replaceWall;
    private $indicatorWall;
    private $dealResult;

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
        $this->playerType = $playerType;

        $this->dicePair = new DicePair();
        $generateStack = function () {
            return new Stack();
        };
        $this->stackList = (new StackList())->fromGenerator(4 * 17, $generateStack);
        $this->drawWall = new LiveWall(true);
        $this->replaceWall = new LiveWall(false);

        $this->init();
    }

    function init() {
        // clear
        $initStack = function (Stack $stack) {
            $stack->init();
        };
        $this->stackList->walk($initStack);

        // 1.Mix the tiles
        $this->initialTileList = $this->tileSet->toTileList()->shuffle();

        // 2.Building the wall
        $chunkList = new ArrayList($this->initialTileList->toChunks(2));
        $setChunk = function (Stack $stack, array $chunk) {
            $stack->setTileChunk($chunk);
            return $stack;
        };
        $this->stackList->fromMapping($this->stackList, $chunkList, $setChunk);

        // 3.Roll two dice
        $diceResult = $this->getDicePair()->roll();

        // 4.Break the wall
        list($drawStackList, $replaceStackList, $indicatorStackList) = $this->stackList->toThreeBreak($diceResult);
        $this->drawWall->init($drawStackList);
        $this->replaceWall->init($replaceStackList);
        $this->indicatorWall = new IndicatorWall($indicatorStackList);

        // 5.The deal
        $this->dealResult = $this->deal($this->playerType);

        // 6.Open dora indicator
        // already done in indicatorWall
    }

    /**
     * @param PlayerType $playerType
     * @return Tile[][] e.g. [E => [1s,2s...] ...]
     */
    private function deal(PlayerType $playerType) {
        $result = $playerType->getSeatWindMap([]);
        foreach ([4, 4, 4, 1] as $drawTileCount) {
            foreach ($result as $k => $notUsed) {
                $nTodo = $drawTileCount;
                while ($nTodo-- > 0) {
                    $result[$k][] = $this->getDrawWall()->outNext();
                }
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getIndicatorWall()->__toString() . ',' . $this->getDrawWall()->__toString();
    }

    /**
     * @return array
     */
    function toJson() {
        return [
            'stacks' => $this->getIndicatorWall()->toJson(),
            'remainTileCount' => $this->getDrawWall()->getRemainTileCount(),
        ];
    }

    /**
     * @return TileSet
     */
    function getTileSet() {
        return $this->tileSet;
    }

    /**
     * @return TileList
     */
    function getInitialTileList() {
        return $this->initialTileList;
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
     * @return LiveWall
     */
    function getDrawWall() {
        return $this->drawWall;
    }

    /**
     * @return LiveWall
     */
    function getReplaceWall() {
        return $this->replaceWall;
    }

    /**
     * @return IndicatorWall
     */
    function getIndicatorWall() {
        return $this->indicatorWall;
    }

    /**
     * @return Tile[][] e.g. [E => [1s,2s...] ...]
     */
    function getDealResult() {
        return $this->dealResult;
    }
}