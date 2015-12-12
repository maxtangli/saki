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

    static function toSortedTiles(array $tiles) {
        $tmp = $tiles;
        usort($tmp, function (Tile $a, Tile $b) {
            return self::getDisplayPriority($a) - self::getDisplayPriority($b);
        });
        return array_reverse($tmp);
    }

    static function getDisplayPriority(Tile $tile) {
        $tileType = $tile->getTileType();
        $bases = [ // mpsESWNCPF
            TileType::CHARACTER_M => 0,
            TileType::DOT_P => 9,
            TileType::BAMBOO_S => 18,

            TileType::EAST_E => 28,
            TileType::SOUTH_S => 29,
            TileType::WEST_W => 30,
            TileType::NORTH_N => 31,

            TileType::RED_C => 32,
            TileType::WHITE_P => 33,
            TileType::GREEN_F => 34,
        ];
        $base = $bases[$tileType->getValue()];
        $plus = $tileType->isSuit() ? $tile->getNumber() : 0;
        return 34 - ($base + $plus);
    }

    /**
     * @param string $s
     * @return TileSortedList
     */
    static function fromString($s) {
        return parent::fromString($s);
    }

    function __construct(array $tiles) {
        parent::__construct($this->toSortedTiles($tiles));
    }

    protected function innerArrayChangedHook() {
        $sortedTiles = $this->toSortedTiles($this->toArray());
        parent::setInnerArray($sortedTiles);
    }

    function toTileSortedList() {
        return new TileSortedList($this->toArray());
    }
}

