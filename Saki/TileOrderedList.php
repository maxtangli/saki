<?php
namespace Saki;

class TileOrderedList extends TileList {
    static function sort(array $tiles) {
        $tmp = $tiles;
        usort($tmp, function (Tile $a, Tile $b) {
            return self::getDisplayPriority($a) - self::getDisplayPriority($b);
        });
        return array_reverse($tmp);
    }

    static function getDisplayPriority(Tile $tile) {
        $tileType = $tile->getTileType();
        $base = [
            TileType::CHARACTER => 0,
            TileType::DOT => 9,
            TileType::BAMBOO => 18,
            TileType::EAST => 28,
            TileType::SOUTH => 29,
            TileType::WEST => 30,
            TileType::NORTH => 31,
            TileType::RED => 32,
            TileType::GREEN => 33,
            TileType::WHITE => 34,
        ][$tileType->getValue()];
        $plus = $tileType->isSuit() ? $tile->getNumber() : 0;
        return 34 - ($base + $plus);
    }

    function __construct(array $tiles) {
        parent::__construct(self::sort($tiles));
    }
}

