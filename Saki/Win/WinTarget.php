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
        $handTileList = $this->getHandTileSortedList(false);
        $valid = ($roundPhase == RoundPhase::getPrivatePhaseInstance() && $handTileList->validPrivatePhaseCount())
            || ($roundPhase == RoundPhase::getPublicPhaseInstance() && $handTileList->validPublicPhaseCount());
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $allTileList count[%s] for $roundPhase[%s].', count($handTileList), $roundPhase)
            );
        }
    }

    function isSelfPhase() {
        return $this->roundData->getRoundPhase() == RoundPhase::getPrivatePhaseInstance();
    }

    function getHandTileSortedList($includePublicTargetTile = true) {
        $handTileSortedList = $this->player->getPlayerArea()->getHandTileSortedList();
        if ($includePublicTargetTile && $this->roundData->getRoundPhase() == RoundPhase::getPublicPhaseInstance()) {
            $handTileSortedList = new TileSortedList($handTileSortedList->toArray());
            $handTileSortedList->push($this->roundData->getTileAreas()->getPublicTargetTile());
        }
        return $handTileSortedList;
    }

    function getDiscardedTileList() {
        return $this->player->getPlayerArea()->getDiscardedTileList();
    }

    function getDeclaredMeldList() {
        return $this->player->getPlayerArea()->getDeclaredMeldList();
    }

    function getAllTileSortedList($includePublicTargetTile = true) {
        $sortedList = new TileSortedList($this->getHandTileSortedList($includePublicTargetTile)->toArray());
        foreach ($this->getDeclaredMeldList() as $meld) {
            $sortedList->push($meld->toArray());
        }
        return $sortedList;
    }

    function getWinTile() {
        $roundPhase = $this->roundData->getRoundPhase();
        if ($roundPhase == RoundPhase::getPrivatePhaseInstance()) {
            return $this->player->getPlayerArea()->getCandidateTile();
        } elseif ($roundPhase == RoundPhase::getPublicPhaseInstance()) {
            return $this->roundData->getTileAreas()->getPublicTargetTile();
        } else {
            throw new \LogicException();
        }
    }

    function isReach() {
        return $this->player->getPlayerArea()->isReach();
    }

    function getSelfWind() {
        return $this->player->getSelfWind();
    }

    function getRoundWind() {
        return $this->roundData->getRoundWindData()->getRoundWind();
    }

    function toSubTarget(MeldList $handMeldList) {
        return new WinSubTarget($handMeldList, $this->player, $this->roundData);
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
        return $this->getHandTileSortedList()->isAll(function (Tile $tile) {
            return $tile->isSuit();
        });
    }
}