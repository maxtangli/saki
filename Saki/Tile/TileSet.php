<?php
namespace Saki\Tile;

/**
 * note: not good to extends TileList since modify not allowed.
 * @package Saki\Tile
 */
class TileSet extends TileList {
    static function getStandardTileSet() {
        $s = '111122223333444455556666777788889999m' .
            '111122223333444455556666777788889999p' .
            '111122223333444455556666777788889999s' .
            'EEEESSSSWWWWNNNNCCCCPPPPFFFF';
        $tileList = TileList::fromString($s);
        return new self($tileList);
    }

    function __construct(TileList $baseTileList) {
        parent::__construct($baseTileList->toArray());
    }

    function getUniqueTiles() {
        return array_unique($this->toArray());
    }
}