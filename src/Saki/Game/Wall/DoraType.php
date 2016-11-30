<?php

namespace Saki\Game\Wall;

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\Enum;

/**
 * @package Saki\Game
 */
class DoraType extends Enum {
    const DORA = 0;
    const URA_DORA = 1;
    const RED_DORA = 2;

    /**
     * @param TileList $hand
     * @param TileList|null $doraIndicatorList
     * @return int
     */
    function getHandFan(TileList $hand, TileList $doraIndicatorList = null) {
        $getTileDora = function (Tile $tile) use ($doraIndicatorList) {
            return $this->getTileFan($tile, $doraIndicatorList);
        };
        return $hand->getSum($getTileDora);
    }

    /**
     * @param Tile $tile
     * @param TileList|null $doraIndicatorList
     * @return int
     */
    function getTileFan(Tile $tile, TileList $doraIndicatorList = null) {
        switch ($this->getValue()) {
            case self::DORA:
            case self::URA_DORA:
                $toDoraCount = function (Tile $indicator) use ($tile) {
                    return $tile == $indicator->getNextTile(1) ? 1 : 0;
                };
                return $doraIndicatorList->getSum($toDoraCount);
            case self::RED_DORA:
                return $tile->isRedDora() ? 1 : 0;
        }
        throw new \LogicException();
    }
}