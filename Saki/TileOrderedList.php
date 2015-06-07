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

    /**
     * @param string $s
     * @param bool $readonly
     * @return TileOrderedList
     */
    static function fromString($s, $readonly = false) {
        return parent::fromString($s, $readonly);
    }

    function __construct(array $tiles, $readonly = false) {
        parent::__construct($this->sort($tiles), $readonly);
    }

    protected function setInnerArray($innerArray) {
        parent::setInnerArray(self::sort($innerArray));
    }
}

