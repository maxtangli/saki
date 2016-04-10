<?php
namespace Saki\Win;

use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Game\RoundTurn;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;

// todo move yaku specific logic into XXXYaku
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
    function getRoundTurn() {
        return $this->round->getTurnManager()->getRoundTurn();
    }
    
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

    function getOpenHistory() {
        return $this->round->getTileAreas()->getOpenHistory();
    }

    function getOutsideRemainTileAmount(Tile $tile) {
        return $this->round->getTileAreas()->getOutsideRemainTileAmount($tile);
    }

    function getWallRemainTileAmount() {
        return $this->round->getTileAreas()->getWall()->getRemainTileCount();
    }

    // about target player

    function getPublicHand() {
        return $this->player->getTileArea()->getHand()->getPublic();
    }

    function getPrivateHand() {
        return $this->player->getTileArea()->getHand()->getPrivate();
    }

    function getPrivateComplete() {
        return $this->player->getTileArea()->getHand()->getPrivatePlusDeclare();
    }

    function getDeclaredMeldList() {
        return $this->player->getTileArea()->getHand()->getDeclare();
    }

    function getDiscardedTileList() {
        return $this->player->getTileArea()->getDiscardedReference();
    }

    function isConcealed() {
        return $this->player->getTileArea()->getHand()->isConcealed();
    }

    function getReachStatus() {
        return $this->player->getTileArea()->getReachStatus();
    }

    function isFirstTurnWin() {
        return $this->round->getTileAreas()->isFirstTurnWin($this->player);
    }

    function getTileOfTargetTile() {
        return $this->getActPlayer()->getTileArea()->getHand()->getTarget()->getTile();
    }

    function isKingSTileWin() {
        return $this->getActPlayer()->getTileArea()->getHand()
            ->getTarget()->isKingSTile();
    }

    function isRobbingAQuadWin() {
        return $this->getActPlayer()->getTileArea()->getHand()->getTarget()->isRobQuadTile();
    }

    function isHeavenlyWin() {
        $actPlayer = $this->getActPlayer();
        return $this->isFirstTurnNoDeclare($actPlayer)
        && $this->round->getPhaseState()->getRoundPhase()->isPrivate()
        && $actPlayer->getTileArea()->getPlayerWind()->isDealer();
    }

    function isEarthlyWin() {
        $actPlayer = $this->getActPlayer();
        return $this->isFirstTurnNoDeclare($actPlayer)
        && $this->round->getPhaseState()->getRoundPhase()->isPrivate()
        && $actPlayer->getTileArea()->getPlayerWind()->isLeisureFamily();
    }

    function isHumanlyWin() {
        $actPlayer = $this->getActPlayer();
        return $this->isFirstTurnNoDeclare($actPlayer)
        && $this->round->getPhaseState()->getRoundPhase()->isPublic()
        && $actPlayer->getTileArea()->getDiscard()->isEmpty();
    }

    protected function isFirstTurnNoDeclare(Player $player) {
        $r = $this->round;
        return $r->getTurnManager()->getRoundTurn()->getGlobalTurn() == 1
        && !$r->getTileAreas()->getDeclareHistory()->hasDeclare(
            new RoundTurn(1, $player->getTileArea()->getPlayerWind())
        );
    }

    function getPlayerWind() {
        return $this->player->getTileArea()->getPlayerWind();
    }

    function getSelfWindTile() {
        return $this->player->getTileArea()->getPlayerWind()->getWindTile();
    }

    function getDoraFacade() {
        return $this->round->getTileAreas()->getWall()->getDoraFacade();
    }
}