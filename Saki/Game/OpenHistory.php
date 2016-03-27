<?php

namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayLikeObject;

/**
 * Record discard and plusKong tiles for furiten judge.
 * @package Saki\Game
 */
class OpenHistory {

    /**
     * @var ArrayLikeObject
     */
    private $a;

    function __construct() {
        $this->a = new ArrayLikeObject([]);
    }

    function __toString() {
        return $this->a->__toString("\n");
    }

    function reset() {
        $this->a->clear();
    }

    /**
     * @param Tile $mySelfWind
     * @param int $fromTurn
     * @param null $fromSelfWind
     * @param bool|false $excludedLastTile
     * @return TileList
     */
    function getOther(Tile $mySelfWind, $fromTurn = 1, $fromSelfWind = null, $excludedLastTile = false) {
        return $this->getImpl(false, $mySelfWind, $fromTurn, $fromSelfWind, $excludedLastTile);
    }

    /**
     * @param Tile $mySelfWind
     * @param int $fromTurn
     * @return TileList
     */
    function getSelf(Tile $mySelfWind, $fromTurn = 1) {
        return $this->getImpl(true, $mySelfWind, $fromTurn);
    }

    /**
     * @return TileList
     */
    function getAll() {
        $tiles = $this->a->toArray(function(OpenHistoryItem $item) {
            return $item->getDiscardedTile();
        });
        return new TileList($tiles);
    }

    /**
     * @param $isSelf
     * @param Tile $mySelfWind
     * @param int $fromTurn
     * @param null $fromSelfWind
     * @param bool|false $excludedLastTile
     * @return TileList
     */
    private function getImpl($isSelf, Tile $mySelfWind, $fromTurn = 1, $fromSelfWind = null, $excludedLastTile = false) {
        $actualFromSelfWind = $fromSelfWind ?? Tile::fromString('E');

        $notUsedParam = $actualFromSelfWind;
        $compareItem = new OpenHistoryItem($fromTurn, $actualFromSelfWind, $notUsedParam); // validate
        $match = function(OpenHistoryItem $item) use ($isSelf, $mySelfWind, $compareItem) {
            $matchIsSelf = $isSelf ? $item->getSelfWind() == $mySelfWind : $item->getSelfWind() != $mySelfWind;
            $matchOrder = $item->validLaterItemOf($compareItem, true);
            return $matchIsSelf && $matchOrder;
        };

        /** @var TileList $openTileList */
        $openTileList = $this->a->toReducedValue(function (TileList $targetDiscardTileList, OpenHistoryItem $item) use ($match) {
            if ($match($item)) {
                $targetDiscardTileList->push($item->getDiscardedTile());
            }
            return $targetDiscardTileList;
        }, new TileList([]));

        if ($excludedLastTile && $openTileList->count() > 0) {
            $openTileList->pop();
        }

        return $openTileList;
    }

    function record($currentTurn, Tile $mySelfWind, Tile $tile) {
        $newItem = new OpenHistoryItem($currentTurn, $mySelfWind, $tile); // validate
        if ($this->a->count() > 0) {
            /** @var OpenHistoryItem $lastItem */
            $lastItem = $this->a->getLast();
//            $valid = $newItem->validLaterItemOf($lastItem, false);
            $valid = $newItem->validLaterItemOf($lastItem, true);
            if (!$valid) {
                throw new \InvalidArgumentException(
                    sprintf('param item [%s] should be valid later item of [%s]', $newItem, $lastItem)
                );
            }
        }

        $this->a->push($newItem);
    }
}