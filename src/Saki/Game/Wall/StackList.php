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
    static function createByCount(int $n) {
        $generateStack = function () {
            return new Stack();
        };
        return (new static())->fromGenerator($n, $generateStack);
    }

    /**
     * @param TileList $tileList
     * @return static
     */
    static function createByTileList(TileList $tileList) {
        $stackList = static::createByCount($tileList->count() / 2);
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
}