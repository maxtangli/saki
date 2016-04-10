<?php
namespace Saki\Win;

use Saki\Game\Player;
use Saki\Game\PrevailingWind;
use Saki\Game\Round;
use Saki\Game\Turn;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;

// todo move yaku specific logic into XXXYaku
class WinTarget {
    private $player;
    private $round;

    function __construct(Player $player, Round $round) {
        $this->player = $player;
        $this->round = $round;

        $phase = $round->getPhaseState()->getPhase();
        if (!$phase->isPrivateOrPublic()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid round phase, expect[private or public phase] but given[%s].', $phase)
            );
        }
        // todo validate hand count, target tile
    }

    function toSubTarget(MeldList $handMeldList) {
        return new WinSubTarget($handMeldList, $this->player, $this->round);
    }

    // about round/global

    /**
     * @return PrevailingWind
     */
    function getPrevailingWind() {
        return $this->round->getPrevailingWindData()->getPrevailingWind();
    }

    // todo remove
    function getPrevailingWindTile() {
        return $this->round->getPrevailingWindData()->getPrevailingWind()->getWindTile();
    }

    function getTileSet() {
        return $this->round->getGameData()->getTileSet();
    }

    // about round/current
    function getTurn() {
        return $this->round->getAreas()->getCurrentTurn();
    }

    function getCircleCount() {
        return $this->round->getAreas()->getCurrentTurn()->getCircleCount();
    }

    function isPrivatePhase() {
        return $this->round->getPhaseState()->getPhase()->isPrivate();
    }

    function isPubicPhase() {
        return $this->round->getPhaseState()->getPhase()->isPublic();
    }

    function getActPlayer() {
        return $this->player;
    }

    function getCurrentPlayer() {
        return $this->round->getAreas()->tempGetCurrentPlayer();
    }

    function getOpenHistory() {
        return $this->round->getAreas()->getOpenHistory();
    }

    function getOutsideRemainTileAmount(Tile $tile) {
        return $this->round->getAreas()->getOutsideRemainTileAmount($tile);
    }

    function getWallRemainTileAmount() {
        return $this->round->getAreas()->getWall()->getRemainTileCount();
    }

    // about target player

    function getPublicHand() {
        return $this->player->getArea()->getHand()->getPublic();
    }

    function getPrivateHand() {
        return $this->player->getArea()->getHand()->getPrivate();
    }

    function getPrivateComplete() {
        return $this->player->getArea()->getHand()->getPrivatePlusDeclare();
    }

    function getDeclaredMeldList() {
        return $this->player->getArea()->getHand()->getDeclare();
    }

    function getDiscardedTileList() {
        return $this->player->getArea()->getDiscardedReference();
    }

    function isConcealed() {
        return $this->player->getArea()->getHand()->isConcealed();
    }

    function getReachStatus() {
        return $this->player->getArea()->getReachStatus();
    }

    function isFirstTurnWin() {
        return $this->round->getAreas()->isFirstTurnWin($this->player);
    }

    function getTileOfTargetTile() {
        return $this->getActPlayer()->getArea()->getHand()->getTarget()->getTile();
    }

    function isKingSTileWin() {
        return $this->getActPlayer()->getArea()->getHand()
            ->getTarget()->isKingSTile();
    }

    function isRobbingAQuadWin() {
        return $this->getActPlayer()->getArea()->getHand()->getTarget()->isRobQuadTile();
    }

    function isHeavenlyWin() {
        $actPlayer = $this->getActPlayer();
        return $this->isFirstTurnNoDeclare($actPlayer)
        && $this->round->getPhaseState()->getPhase()->isPrivate()
        && $actPlayer->getArea()->getSeatWind()->isDealer();
    }

    function isEarthlyWin() {
        $actPlayer = $this->getActPlayer();
        return $this->isFirstTurnNoDeclare($actPlayer)
        && $this->round->getPhaseState()->getPhase()->isPrivate()
        && $actPlayer->getArea()->getSeatWind()->isLeisureFamily();
    }

    function isHumanlyWin() {
        $actPlayer = $this->getActPlayer();
        return $this->isFirstTurnNoDeclare($actPlayer)
        && $this->round->getPhaseState()->getPhase()->isPublic()
        && $actPlayer->getArea()->getDiscard()->isEmpty();
    }

    protected function isFirstTurnNoDeclare(Player $player) {
        $r = $this->round;
        return $r->getAreas()->getCurrentTurn()->getCircleCount() == 1
        && !$r->getAreas()->getDeclareHistory()->hasDeclare(
            new Turn(1, $player->getArea()->getSeatWind())
        );
    }

    function getSeatWind() {
        return $this->player->getArea()->getSeatWind();
    }

    // todo remove
    function getSeatWindTile() {
        return $this->player->getArea()->getSeatWind()->getWindTile();
    }

    function getDoraFacade() {
        return $this->round->getAreas()->getWall()->getDoraFacade();
    }
}