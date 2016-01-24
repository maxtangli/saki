<?php
namespace Saki\Tile;

/**
 * @package Saki\Tile
 */
class TileSet extends TileList {

    private static $standTileSet;
    static function getStandardTileSet() {
        self::$standTileSet = self::$standTileSet ?? new self(
                TileList::fromString(
                    '111122223333444455556666777788889999m' .
                    '111122223333444455556666777788889999p' .
                    '111122223333444455556666777788889999s' .
                    'EEEESSSSWWWWNNNNCCCCPPPPFFFF'
                )
            );
        return self::$standTileSet;
    }

    function __construct(TileList $baseTileList) {
        parent::__construct($baseTileList->toArray());
        $this->setWritable(false);
    }

    function getUniqueTiles() {
        return array_unique($this->toArray());
    }
}