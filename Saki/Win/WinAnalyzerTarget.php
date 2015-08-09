<?php
namespace Saki\Win;

use Saki\Game\Player;
use Saki\Game\RoundData;
use Saki\Game\RoundPhase;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileSortedList;

class WinAnalyzerTarget {
    private $player;
    private $roundData;

    function __construct(Player $player, RoundData $roundData) {
        $this->player = $player;
        $this->roundData = $roundData;

        $roundPhase = $roundData->getRoundPhase();
        $allTileList = $this->getAllTileSortedList(false);
        $valid = ($roundPhase==RoundPhase::getPrivatePhaseInstance() && $allTileList->validPrivatePhaseCount())
            || ($roundPhase==RoundPhase::getPublicPhaseInstance() && $allTileList->validPublicPhaseCount());
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $allTileList count[%s] for $roundPhase[%s].', count($allTileList), $roundPhase)
            );
        }
    }

    function getHandTileSortedList($includePublicTargetTile = true) {
        $handTileSortedList = $this->player->getPlayerArea()->getHandTileSortedList();
        if ($includePublicTargetTile && $this->roundData->getRoundPhase()==RoundPhase::getPublicPhaseInstance()) {
            $handTileSortedList = new TileSortedList($handTileSortedList->toArray());
            $handTileSortedList->push($this->roundData->getTileAreas()->getPublicTargetTile());
        }
        return $handTileSortedList;
    }

    function getDiscardedTileList() {
        return $this->player->getPlayerArea()->getDiscardedTileList();;
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
        return $this->player->getPlayerArea()->getCandidateTile();
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
        return new WinAnalyzerSubTarget($handMeldList, $this->player, $this->roundData);
    }

    /**
     * 門前清
     */
    function isConcealed() {
        return count($this->getDeclaredMeldList()) == 0;
    }

    /**
     * 鳴き牌あり
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