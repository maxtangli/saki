<?php
namespace Saki\Win;

use Saki\Game\Player;
use Saki\Game\RoundData;
use Saki\Game\RoundPhase;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileSortedList;

class WinTarget {
    private $player;
    private $roundData;

    function __construct(Player $player, RoundData $roundData) {
        $this->player = $player;
        $this->roundData = $roundData;

        $roundPhase = $roundData->getRoundPhase();
        $handTileList = $player->getPlayerArea()->getHandTileSortedList();
        $valid = ($roundPhase == RoundPhase::getPrivatePhaseInstance() && $handTileList->validPrivatePhaseCount())
            || ($roundPhase == RoundPhase::getPublicPhaseInstance() && $handTileList->validPublicPhaseCount());
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $allTileList count[%s] for $roundPhase[%s].', count($handTileList), $roundPhase)
            );
        }
    }

    function toSubTarget(MeldList $handMeldList) {
        return new WinSubTarget($handMeldList, $this->player, $this->roundData);
    }

    function isPrivatePhase() {
        return $this->roundData->getRoundPhase() == RoundPhase::getPrivatePhaseInstance();
    }

    function isPubicPhase() {
        return $this->roundData->getRoundPhase() == RoundPhase::getPublicPhaseInstance();
    }

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

    function getTileRemainAmount(Tile $tile) {
        return $this->roundData->getTileAreas()->getTileRemainAmount($tile);
    }

    function getWallRemainTileAmount() {
        return $this->roundData->getTileAreas()->getWall()->getRemainTileCount();
    }

    function getTargetTile() {
        $roundPhase = $this->roundData->getRoundPhase();
        if ($roundPhase == RoundPhase::getPrivatePhaseInstance()) {
            return $this->player->getPlayerArea()->getPrivateTargetTile();
        } elseif ($roundPhase == RoundPhase::getPublicPhaseInstance()) {
            return $this->roundData->getTileAreas()->getPublicTargetTile();
        } else {
            throw new \LogicException();
        }
    }

    function getCurrentPlayer() {
        return $this->roundData->getPlayerList()->getCurrentPlayer();
    }

    function getDiscardHistory() {
        return $this->roundData->getTileAreas()->getDiscardHistory();
    }

    function isReach() {
        return $this->player->getPlayerArea()->isReach();
    }

    function getReachTurn() {
        return $this->player->getPlayerArea()->getReachTurn();
    }

    function getSelfWind() {
        return $this->player->getSelfWind();
    }

    function getGlobalTurn() {
        return $this->roundData->getPlayerList()->getGlobalTurn();
    }

    function getRoundWind() {
        return $this->roundData->getRoundWindData()->getRoundWind();
    }

    function getTileSet() {
        return $this->roundData->getTileAreas()->getWall()->getTileSet();
    }

    /**
     * @return bool 門前清?
     */
    function isConcealed() {
        return count($this->getDeclaredMeldList()) == 0;
    }

    /**
     * @return bool 鳴き牌あり
     */
    function isExposed() {
        return !$this->isConcealed();
    }

    function isAllSuit() {
        return $this->getHandTileSortedList(true)->all(function (Tile $tile) {
            return $tile->isSuit();
        });
    }
}