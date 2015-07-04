<?php
namespace Saki\Tile;

class TileSortedList extends TileList {

    static function sort(array $tiles) {
        $tmp = $tiles;
        usort($tmp, function (Tile $a, Tile $b) {
            return self::getDisplayPriority($a) - self::getDisplayPriority($b);
        });
        return array_reverse($tmp);
    }

    static function getDisplayPriority(Tile $tile) {
        $tileType = $tile->getTileType();
        $base = [ // 萬筒
            TileType::CHARACTER => 0,
            TileType::DOT => 9,
            TileType::BAMBOO => 18,
            TileType::EAST => 28,
            TileType::SOUTH => 29,
            TileType::WEST => 30,
            TileType::NORTH => 31,
            TileType::RED => 32,
            TileType::WHITE => 33,
            TileType::GREEN => 34,
        ][$tileType->getValue()];
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
        parent::__construct($this->sort($tiles));
    }

    protected function innerArrayChangedHook() {
        parent::setInnerArray(self::sort($this->toArray()), false);
    }
}

