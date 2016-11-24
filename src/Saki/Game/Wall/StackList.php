<?php
namespace Saki\Game\Wall;

use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Game\Wall
 */
class StackList extends ArrayList {
    /**
     * @param int $n
     * @return static
     */
    static function createByStackCount(int $n) {
        $generateStack = function () {
            return new Stack();
        };
        return (new static())->fromGenerator($n, $generateStack);
    }

    /**
     * tile list
     * 012345
     * s1 s2 s3
     * 1  3  5
     * 0  2  4
     * @param TileList $tileList
     * @return static
     */
    static function createByTileList(TileList $tileList) {
        self::assertTileListEvenCount($tileList);
        $stackList = static::createByStackCount($tileList->count() / 2);
        $stackList->initByTileList($tileList);
        return $stackList;
    }

    /**
     * @param TileList $tileList
     * @return $this
     */
    function initByTileList(TileList $tileList) {
        $valid = ($tileList->count() == $this->count() * 2);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $chunkList = new ArrayList($tileList->toChunks(2));
        $setChunk = function (Stack $stack, array $chunk) {
            $stack->setTileChunk($chunk);
            return $stack;
        };
        return $this->fromMapping($this, $chunkList, $setChunk);
    }

    /**
     * @return TileList
     */
    function toTileList() {
        $selector = function (Stack $stack) {
            return $stack->getTileList();
        };
        return (new TileList())->fromSelectMany($this, $selector);
    }

    private static function assertTileListEvenCount(TileList $tileList) {
        $valid = ($tileList->count() % 2 == 0);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
    }
}