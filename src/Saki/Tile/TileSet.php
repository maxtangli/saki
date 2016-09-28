<?php
namespace Saki\Tile;

use Saki\Util\ReadonlyArrayList;

/**
 * @package Saki\Tile
 */
class TileSet extends TileList {
    use ReadonlyArrayList;
    private static $standardInstance;

    /**
     * WARNING: support at most 1 red tile for each suit-type.
     * @return TileSet
     */
    static function createStandard() {
        self::$standardInstance = self::$standardInstance ?? new self(
                TileList::fromString(
                    '111122223333444455506666777788889999m' .
                    '111122223333444455506666777788889999p' .
                    '111122223333444455506666777788889999s' .
                    'EEEESSSSWWWWNNNNCCCCPPPPFFFF'
                )->toArray()
            );
        return self::$standardInstance;
    }

    /**
     * @return TileList
     */
    function toUniqueTileList() {
        return (new TileList($this->toArray()))->distinct();
    }

    function echoPriorityMap() {
        $toString = function (Tile $tile) {
            return $tile->__toString();
        };
        $toPriority = function (Tile $tile) {
            return $tile->getPriority();
        };

        $list = $this->toArrayList()
            ->distinct(Tile::getEqual(true));

        $a = array_combine($list->toArray($toString), $list->toArray($toPriority));

        foreach ($a as $k => $v) {
            echo sprintf("'$k' => $v,");
        }
    }
}