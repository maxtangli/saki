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
        $this->round = $round;

        $roundPhase = $round->getPhaseState()->getRoundPhase();
        if (!$roundPhase->isPrivateOrPublic()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid round phase, expect[private or public phase] but given[%s].', $roundPhase)
            );
        }

        // todo validate hand count, target tile
    }

    function toSubTarget(MeldList $handMeldList) {
        return new WinSubTarget($handMeldList, $this->player, $this->round);
    }

    // about round/global

    function getRoundWind() {
        return $this->round->getRoundWindData()->getRoundWind();
    }

    function getTileSet() {
        return $this->round->getGameData()->getTileSet();
    }

    // about round/current
    function getGlobalTurn() {
        return $this->round->getTurnManager()->getGlobalTurn();
    }

    function isPrivatePhase() {
        return $this->round->getPhaseState()->getRoundPhase()->isPrivate();
    }

    function isPubicPhase() {
        return $this->round->getPhaseState()->getRoundPhase()->isPublic();
    }

    function getActPlayer() {
        return $this->player;
    }

    function getCurrentPlayer() {
        return $this->round->getTurnManager()->getCurrentPlayer();
    }

    function getTileOfTargetTile() {
        return $this->round->getTileAreas()->getTargetTile()->getTile();
    }

    function getDiscardHistory() {
        return $this->round->getTileAreas()->getDiscardHistory();
    }

    function getOutsideRemainTileAmount(Tile $tile) {
        return $this->round->getTileAreas()->getOutsideRemainTileAmount($tile);
    }

    function getWallRemainTileAmount() {
        return $this->round->getTileAreas()->getWall()->getRemainTileCount();
    }

    // about target player
    function getPublicHand() {
        return $this->round->getTileAreas()->getPublicHand($this->player);
    }

    function getPrivateHand() {
        return $this->round->getTileAreas()->getPrivateHand($this->player);
    }

    function getPrivateFull() {
        return $this->round->getTileAreas()->getPrivateFull($this->player);
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
        return $this->round->getTileAreas()->isFirstTurnWin($this->player);
    }

    function isKingSTileWin() {
        return $this->round->getTileAreas()->getTargetTile()->isKingSTile();
    }

    function isRobbingAQuadWin() {
        return $this->round->getTileAreas()->getTargetTile()->isRobQuadTile();
    }

    function getReachTurn() {
        return $this->player->getTileArea()->getReachGlobalTurn();
    }

    function getSelfWind() {
        return $this->player->getSelfWind();
    }
}