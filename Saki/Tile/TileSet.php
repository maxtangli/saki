<?php
namespace Saki\Tile;

class TileSet extends TileSortedList {
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
}