<?php
namespace Saki\Win;

use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;

class WinTarget {
    private $player;
    private $round;

    function __construct(Player $player, Round $round) {
        $this->player = $player;
        $this->roundData = $round;

        $roundPhase = $round->getPhaseState()->getRoundPhase();
        if (!$roundPhase->isPrivateOrPublic()) {
            throw new \InvalidArgumentException();
        }

        // todo validate hand count, target tile
    }

    function toSubTarget(MeldList $handMeldList) {
        return new WinSubTarget($handMeldList, $this->player, $this->roundData);
    }

    // about round/global

    function getRoundWind() {
        return $this->roundData->getRoundWindData()->getRoundWind();
    }

    function getTileSet() {
        return $this->roundData->getGameData()->getTileSet();
    }

    // about round/current
    function getGlobalTurn() {
        return $this->roundData->getTurnManager()->getGlobalTurn();
    }

    function isPrivatePhase() {
        return $this->roundData->getPhaseState()->getRoundPhase()->isPrivate();
    }

    function isPubicPhase() {
        return $this->roundData->getPhaseState()->getRoundPhase()->isPublic();
    }

    function getActPlayer() {
        return $this->player;
    }

    function getCurrentPlayer() {
        return $this->roundData->getTurnManager()->getCurrentPlayer();
    }

    function getTileOfTargetTile() {
        return $this->roundData->getTileAreas()->getTargetTile()->getTile();
    }

    function getDiscardHistory() {
        return $this->roundData->getTileAreas()->getDiscardHistory();
    }

    function getOutsideRemainTileAmount(Tile $tile) {
        return $this->roundData->getTileAreas()->getOutsideRemainTileAmount($tile);
    }

    function getWallRemainTileAmount() {
        return $this->roundData->getTileAreas()->getWall()->getRemainTileCount();
    }

    // about target player
    function getPublicHand() {
        return $this->roundData->getTileAreas()->getPublicHand($this->player);
    }

    function getPrivateHand() {
        return $this->roundData->getTileAreas()->getPrivateHand($this->player);
    }

    function getPrivateFull() {
        return $this->roundData->getTileAreas()->getPrivateFull($this->player);
    }

    function getDeclaredMeldList() {
        return $this->player->getTileArea()->getDeclaredMeldListReference();
    }

    function getDiscardedTileList() {
        return $this->player->getTileArea()->getDiscardedReference();
    }

    function isConcealed() {
        return $this->player->getTileArea()->isConcealed();
    }

    function isReach() {
        return $this->player->getTileArea()->isReach();
    }

    function isDoubleReach() {
        return $this->player->getTileArea()->isDoubleReach();
    }

    function isFirstTurnWin() {
        return $this->roundData->getTileAreas()->isFirstTurnWin($this->player);
    }

    function isKingSTileWin() {
        return $this->roundData->getTileAreas()->getTargetTile()->isKingSTile();
    }

    function getReachTurn() {
        return $this->player->getTileArea()->getReachGlobalTurn();
    }

    function getSelfWind() {
        return $this->player->getSelfWind();
    }
}