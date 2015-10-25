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

        $roundPhase = $roundData->getTurnManager()->getRoundPhase();
        $handTileList = $player->getTileArea()->getHandTileSortedList();

        if (!$roundPhase->isPrivateOrPublic()) {
            throw new \InvalidArgumentException();
        }

        $isPrivate = $roundPhase->getValue() == RoundPhase::PRIVATE_PHASE;
        $validHandTileCount = $handTileList->isPrivateOrPublicHandCount($isPrivate);
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
        return $this->roundData->getTurnManager()->getGlobalTurn();
    }

    function isPrivatePhase() {
        return $this->roundData->getTurnManager()->getRoundPhase()->getValue() == RoundPhase::PRIVATE_PHASE;
    }

    function isPubicPhase() {
        return $this->roundData->getTurnManager()->getRoundPhase()->getValue() == RoundPhase::PUBLIC_PHASE;
    }

    function getCurrentPlayer() {
        return $this->roundData->getTurnManager()->getCurrentPlayer();
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
        return $this->player->getTileArea()->getDeclaredMeldList();
    }

    function getAllTileSortedList($includePublicTargetTile) {
        return $this->roundData->getTileAreas()->toPlayerAllTileList($this->player, $includePublicTargetTile);
    }

    function getDiscardedTileList() {
        return $this->player->getTileArea()->getDiscardedTileList();
    }

    function isConcealed() {
        return $this->player->getTileArea()->isConcealed();
    }

    function isExposed() {
        return $this->player->getTileArea()->isExposed();
    }

    function isReach() {
        return $this->player->getTileArea()->isReach();
    }

    function isDoubleReach() {
        return $this->player->getTileArea()->isDoubleReach();
    }

    function getReachTurn() {
        return $this->player->getTileArea()->getReachTurn();
    }

    function getSelfWind() {
        return $this->player->getSelfWind();
    }
}