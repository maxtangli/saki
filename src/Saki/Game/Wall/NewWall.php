<?php
namespace Saki\Game\Wall;

use Saki\Game\DicePair;
use Saki\Game\Tile\TileList;
use Saki\Game\Tile\TileSet;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

class NewWall {
    // immutable
    private $tileSet;
    private $dicePair;
    // variable
    private $stackList;

    function __construct(TileSet $tileSet) {
        $this->tileSet = $tileSet;
        $this->dicePair = new DicePair();

        $generateStack = function () {
            return new Stack();
        };
        $this->stackList = (new ArrayList())->fromGenerator(4 * 17, $generateStack);
    }

    function init() {
        $initStack = function (Stack $stack) {
            $stack->init();
        };
        $this->stackList->walk($initStack);
    }

    /**
     * @return DicePair
     */
    function getDicePair() {
        return $this->dicePair;
    }

    function prepare() {
        // Mix the tiles
        $tileList = $this->tileSet->toTileList()->shuffle();

        // Building the wall
        $chunkList = new ArrayList($tileList->toChunks(2));
        $setChunk = function (Stack $stack, array $chunk) {
            $stack->setTileChunk($chunk);
            return $stack;
        };
        $this->stackList->fromZipped($this->stackList, $chunkList, $setChunk);

        // Roll two dice
        $diceResult = $this->getDicePair()->roll();
        $dealWindIndex = $diceResult % 4;

        // Break the wall
        // E       S        W        N
        // 0       1        2        3
        // 0...16, 17...33, 34...50, 51...67
        // draw: --
        // replace: ++

        $last = $dealWindIndex * 17 - 1;
        $start = $last - $diceResult;
        // todo DeadWall, DrawWall

        // The deal
        $dealResult = [
            new TileList(),
            new TileList(),
            new TileList(),
            new TileList(),
        ];
        $this->currentDrawIndex = $start;
        foreach ([4, 4, 4, 1] as $drawCount) {
            /** @var TileList $tileList */
            foreach ($dealResult as $tileList) {
                while ($drawCount-- > 0) {
                    $tileList->insertLast($this->draw());
                }
            }
        }

        // Open dora indicator
        // todo

        return $dealResult;
    }

    private $currentDrawIndex;

    function draw() {
        /** @var \Saki\Game\Stack $currentStack */
        $currentStack = $this->stackList[$this->currentDrawIndex];
        $tile = $currentStack->pop();

        if ($currentStack->isEmpty()) {
            $this->currentDrawIndex = Utils::normalizedMod($this->currentDrawIndex - 1, 68);
        }

        return $tile;
    }
}