<?php

namespace Saki\Game;

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
    function getLastOpenOrFalse(SeatWind $mySeatWind) {
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
    function getSelf(SeatWind $mySeatWind) {
        return $this->getImpl(true, $mySeatWind, Turn::createFirst(), false);
    }

    /**
     * Return other's open tiles since $fromTurn, exclude last one tile if exist since it's target tile.
     * Used in: reach furiten, turn furiten.
     * @param SeatWind $mySeatWind
     * @param Turn $fromTurn
     * @return TileList
     */
    function getOther(SeatWind $mySeatWind, Turn $fromTurn) {
        return $this->getImpl(false, $mySeatWind, $fromTurn, true);
    }

    /**
     * Return all player's discard TileList.
     * Note that Area.discard is not used since it may lacks tiles by chow, pung, kong etc.
     * Used in: FourWindDraw.
     * @return TileList
     */
    function getAllDiscard() {
        $discardRecords = $this->list->getCopy()->where(function (OpenRecord $record) {
            return $record->isDiscard();
        });
        return (new TileList())->fromSelect($discardRecords, function (OpenRecord $record) {
            return $record->getTile();
        });
    }

    /**
     * @param bool $require Require self's open TileList if true, other's open TileList otherwise.
     * @param SeatWind $selfActor
     * @param Turn $fromTurn
     * @param bool $excludeLastTile
     * @return TileList
     */
    private function getImpl(bool $require, SeatWind $selfActor, Turn $fromTurn, bool $excludeLastTile) {
        // note: the match logic do not belongs to OpenRecord,
        // since this is a private implementation that varies, not a stable behaviour of OpenRecord.
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
}