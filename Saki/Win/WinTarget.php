<?php
namespace Saki\Win;

use Saki\Game\Player;
use Saki\Game\RoundData;
use Saki\Game\RoundPhase;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;

class WinTarget {
    private $player;
    private $roundData;

    function __construct(Player $player, RoundData $roundData) {
        $this->player = $player;
        $this->roundData = $roundData;

        $roundPhase = $roundData->getRoundPhase();
        $handTileList = $player->getPlayerArea()->getHandTileSortedList();

        if (!$roundPhase->isPrivateOrPublic()) {
            throw new \InvalidArgumentException();
        }

        $isPrivate = $roundPhase->getValue() == RoundPhase::PRIVATE_PHASE;
        $validHandTileCount = $handTileList->isPrivateOrPublicPhaseCount($isPrivate);
        if (!$validHandTileCount) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $handTileList[%s] count[%s] of for $roundPhase[%s].',
                    $handTileList, $handTileList->count(), $roundPhase)
            );
        }
    }

    function toSubTarget(MeldList $handMeldList) {
        return new WinSubTarget($handMeldList, $this->player, $this->roundData);
    }

    // about round/global

    function getRoundWind() {
        return $this->roundData->getRoundWindData()->getRoundWind();
    }

    function getTileSet() {
        return $this->roundData->getTileAreas()->getWall()->getTileSet();
    }

    // about round/current
    function getGlobalTurn() {
        return $this->roundData->getPlayerList()->getGlobalTurn();
    }

    function isPrivatePhase() {
        return $this->roundData->getRoundPhase()->getValue() == RoundPhase::PRIVATE_PHASE;
    }

    function isPubicPhase() {
        return $this->roundData->getRoundPhase()->getValue() == RoundPhase::PUBLIC_PHASE;
    }

    function getCurrentPlayer() {
        return $this->roundData->getPlayerList()->getCurrentPlayer();
    }

    function getTargetTile() {
        return $this->roundData->getTileAreas()->getTargetTile();
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

    function getHandTileSortedList($includePublicTargetTile) {
        return $this->roundData->getTileAreas()->toPlayerHandTileList($this->player, $includePublicTargetTile);
    }

    function getDeclaredMeldList() {
        return $this->player->getPlayerArea()->getDeclaredMeldList();
    }

    function getAllTileSortedList($includePublicTargetTile) {
        return $this->roundData->getTileAreas()->toPlayerAllTileList($this->player, $includePublicTargetTile);
    }

    function getDiscardedTileList() {
        return $this->player->getPlayerArea()->getDiscardedTileList();
    }

    function isConcealed() {
        return $this->player->getPlayerArea()->isConcealed();
    }

    function isExposed() {
        return $this->player->getPlayerArea()->isExposed();
    }

    function isReach() {
        return $this->player->getPlayerArea()->isReach();
    }

    function isDoubleReach() {
        return $this->player->getPlayerArea()->isDoubleReach();
    }

    function getReachTurn() {
        return $this->player->getPlayerArea()->getReachTurn();
    }

    function getSelfWind() {
        return $this->player->getSelfWind();
    }
}