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
     * @param PlayerWind $myPlayerWind
     * @return RoundTurn
     */
    function getLastOpenOrFalse(PlayerWind $myPlayerWind) {
        $myList = $this->list->getCopy()->where(function (OpenRecord $record) use ($myPlayerWind) {
            return $record->getActor() == $myPlayerWind;
        });

        if ($myList->isEmpty()) {
            return false;
        }

        /** @var OpenRecord $lastRecord */
        $lastRecord = $myList->getLast();
        return $lastRecord->getRoundTurn();
    }

    /**
     * Return self's open tiles since first RoundTurn.
     * Used in: discard furiten.
     * @param PlayerWind $myPlayerWind
     * @return TileList
     */
    function getSelf(PlayerWind $myPlayerWind) {
        return $this->getImpl(true, $myPlayerWind, RoundTurn::createFirst(), false);
    }

    /**
     * Return other's open tiles since $fromRoundTurn, exclude last one tile if exist since it's target tile.
     * Used in: reach furiten, turn furiten.
     * @param PlayerWind $myPlayerWind
     * @param RoundTurn $fromRoundTurn
     * @return TileList
     */
    function getOther(PlayerWind $myPlayerWind, RoundTurn $fromRoundTurn) {
        return $this->getImpl(false, $myPlayerWind, $fromRoundTurn, true);
    }

    /**
     * Return all player's discard TileList.
     * Note that Area.discard is not used since it may lacks tiles by chow, pong, kong etc.
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
     * @param PlayerWind $selfActor
     * @param RoundTurn $fromRoundTurn
     * @param bool $excludeLastTile
     * @return TileList
     */
    private function getImpl(bool $require, PlayerWind $selfActor, RoundTurn $fromRoundTurn, bool $excludeLastTile) {
        // note: the match logic do not belongs to OpenRecord,
        // since this is a private implementation that varies, not a stable behaviour of OpenRecord.
        $match = function (OpenRecord $record) use ($require, $selfActor, $fromRoundTurn) {
            $isSelfActorRecord = $record->getActor() == $selfActor;
            $matchRequire = $isSelfActorRecord == $require;
            $matchRoundTurn = $record->getRoundTurn()->isAfterOrSame($fromRoundTurn);
            return $matchRequire && $matchRoundTurn;
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