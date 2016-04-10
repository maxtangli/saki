<?php
namespace Saki\Tile;

use Saki\Util\ReadonlyArrayList;

/**
 * @package Saki\Tile
 */
class TileSet extends TileList {
    use ReadonlyArrayList;
    private static $standardInstance;

    static function createStandard() {
        self::$standardInstance = self::$standardInstance ?? new self(
                TileList::fromString(
                    '111122223333444455556666777788889999m' .
                    '111122223333444455556666777788889999p' .
                    '111122223333444455556666777788889999s' .
                    'EEEESSSSWWWWNNNNCCCCPPPPFFFF'
                )->toArray()
            );
        return self::$standardInstance;
    }

    // note not used currently
    function getUniqueTiles() {
        return array_unique($this->toArray());
    }
}