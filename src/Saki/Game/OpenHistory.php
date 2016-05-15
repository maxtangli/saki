<?php

namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * History of open tiles.
 * Used in: furiten analyze.
 * @package Saki\Game
 */
class OpenHistory {
    /**
     * @var ArrayList An ArrayList with ascend OpenRecord values.
     */
    private $list;

    function __construct() {
        $this->list = new ArrayList();
    }

    function reset() {
        $this->list->removeAll();
    }

    /**
     * @param OpenRecord $record
     */
    function record(OpenRecord $record) {
        if (!$record->validNewOf($this->list)) {
            throw new \InvalidArgumentException();
        }

        $this->list->insertLast($record);
    }

    /**
     * @param SeatWind $mySeatWind
     * @return Turn
     */
    function getLastOpenTurnOrFalse(SeatWind $mySeatWind) {
        $myList = $this->list->getCopy()->where(function (OpenRecord $record) use ($mySeatWind) {
            return $record->getActor() == $mySeatWind;
        });

        if ($myList->isEmpty()) {
            return false;
        }

        /** @var OpenRecord $lastRecord */
        $lastRecord = $myList->getLast();
        return $lastRecord->getTurn();
    }

    /**
     * Return self's open tiles since first Turn.
     * Used in: discard furiten.
     * @param SeatWind $mySeatWind
     * @return TileList
     */
    function getSelfOpen(SeatWind $mySeatWind) {
        return $this->getImpl(true, $mySeatWind, Turn::createFirst(), false);
    }

    /**
     * Return other's open tiles since $fromTurn, exclude last one tile if exist since it's target tile.
     * Used in: reach furiten, turn furiten.
     * @param SeatWind $mySeatWind
     * @param Turn $fromTurn
     * @return TileList
     */
    function getOtherOpen(SeatWind $mySeatWind, Turn $fromTurn) {
        return $this->getImpl(false, $mySeatWind, $fromTurn, true);
    }
    
    /**
     * @param bool $require Require self's open TileList if true, other's open TileList otherwise.
     * @param SeatWind $selfActor
     * @param Turn $fromTurn
     * @param bool $excludeLastTile
     * @return TileList
     */
    private function getImpl(bool $require, SeatWind $selfActor, Turn $fromTurn, bool $excludeLastTile) {
        // design note: the match logic do not belongs to OpenRecord,
        // since this IS a specific implementation that varies, NOT a general stable behaviour of OpenRecord.
        $match = function (OpenRecord $record) use ($require, $selfActor, $fromTurn) {
            $isSelfActorRecord = $record->getActor() == $selfActor;
            $matchRequire = $isSelfActorRecord == $require;
            $matchTurn = $record->getTurn()->isAfterOrSame($fromTurn);
            return $matchRequire && $matchTurn;
        };
        $result = $this->list->getCopy()->where($match);

        if (!$result->isEmpty() && $excludeLastTile) {
            $result->removeLast();
        }

        return (new TileList())->fromSelect($result, function (OpenRecord $record) {
            return $record->getTile();
        });
    }

    /**
     * @param SeatWind $seatWind
     * @return TileList
     */
    function getSelfDiscard(SeatWind $seatWind) {
        $isSelfDiscard = function (OpenRecord $record) use($seatWind) {
            return $record->isSelfDiscard($seatWind);
        };
        $discardTiles = $this->list->getCopy()
            ->where($isSelfDiscard)
            ->toArray(OpenRecord::getToTileCallback());
        return new TileList($discardTiles);
    }

    /**
     * Used in: FourWindDraw
     * @return bool
     */
    function isFourSameWindDiscard() {
        $tileArrayList = $this->list->toArrayList(OpenRecord::getToTileCallback());
        return $tileArrayList->count() == 4
        && $tileArrayList->isSame()
        && $tileArrayList[0]->isWind();
    }

    /**
     * Used in: chow, pung, kong.
     */
    function setLastDiscardDeclared() {
        /** @var OpenRecord $last */
        $last = $this->list->getLast(); // validate exist
        $replace = $last->toDeclared(); // validate isDiscard
        $this->list->replaceAt($this->list->count() - 1, $replace);
    }
}