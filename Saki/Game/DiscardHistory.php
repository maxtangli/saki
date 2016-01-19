<?php

namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayLikeObject;

class DiscardHistory {

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

    function getOtherDiscardTileList(Tile $mySelfWind, $fromTurn = 1, $fromSelfWind = null, $excludedLastTile = false) {
        return $this->getDiscardTileListImpl(false, $mySelfWind, $fromTurn, $fromSelfWind, $excludedLastTile);
    }

    function getSelfDiscardTileList(Tile $mySelfWind, $fromTurn = 1) {
        return $this->getDiscardTileListImpl(true, $mySelfWind, $fromTurn);
    }

    function getAllDiscardTileList() {
        $tiles = $this->a->toArray(function(DiscardHistoryItem $item) {
            return $item->getDiscardedTile();
        });
        return new TileList($tiles);
    }

    private function getDiscardTileListImpl($isSelf, Tile $mySelfWind, $fromTurn = 1, $fromSelfWind = null, $excludedLastTile = false) {
        $actualFromSelfWind = $fromSelfWind ?: Tile::fromString('E');

        $notUsedParam = $actualFromSelfWind;
        $compareItem = new DiscardHistoryItem($fromTurn, $actualFromSelfWind, $notUsedParam); // validate
        $match = function(DiscardHistoryItem $item) use ($isSelf, $mySelfWind, $compareItem) {
            $matchIsSelf = $isSelf ? $item->getSelfWind() == $mySelfWind : $item->getSelfWind() != $mySelfWind;
            $matchOrder = $item->validLaterItemOf($compareItem, true);
            return $matchIsSelf && $matchOrder;
        };

        /** @var TileList $discardTileList */
        $discardTileList = $this->a->toReducedValue(function (TileList $targetDiscardTileList, DiscardHistoryItem $item) use ($match) {
            if ($match($item)) {
                $targetDiscardTileList->push($item->getDiscardedTile());
            }
            return $targetDiscardTileList;
        }, new TileList([]));

        if ($excludedLastTile && $discardTileList->count() > 0) {
            $discardTileList->pop();
        }

        return $discardTileList;
    }

    function recordDiscardTile($currentTurn, Tile $mySelfWind, Tile $tile) {
        $newItem = new DiscardHistoryItem($currentTurn, $mySelfWind, $tile); // validate
        if ($this->a->count() > 0) {
            /** @var DiscardHistoryItem $lastItem */
            $lastItem = $this->a->getLast();
            $valid = $newItem->validLaterItemOf($lastItem);
            if (!$valid) {
                throw new \InvalidArgumentException(
                    sprintf('param item [%s] should be valid later item of [%s]', $newItem, $lastItem)
                );
            }
        }

        $this->a->push($newItem);
    }
}