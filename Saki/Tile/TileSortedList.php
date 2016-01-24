<?php
namespace Saki\Tile;

/**
 * WARNING: for handTileList only since not optimized and slow for large cases.
 *
 * benchmark
 * - TileSortedList( 14).pop(4) = 1ms
 * - TileSortedList(140).pop(4)= 15ms
 * @package Saki\Tile
 */
class TileSortedList extends TileList {

    /**
     * @param string $s
     * @return TileSortedList
     */
    static function fromString($s) {
        // works since
        // - parent implemented by new static()
        // - onInnerArrayChanged() called by ArrayLikeObject constructor
        return parent::fromString($s);
    }

    protected function innerArrayChangedHook() {
        $this->sort();
    }
}