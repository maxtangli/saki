<?php
namespace Saki\Win;

use Saki\Game\Player;
use Saki\Game\RoundData;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileSortedList;

class WinAnalyzerTarget {
    private $player;
    private $roundData;

    function __construct(Player $player, RoundData $roundData) {
        $this->player = $player;
        $this->roundData = $roundData;
    }

    function getHandTileSortedList() {
        return $this->player->getPlayerArea()->getHandTileSortedList();
    }

    function getDiscardedTileList() {
        return $this->player->getPlayerArea()->getDiscardedTileList();;
    }

    function getDeclaredMeldList() {
        return $this->player->getPlayerArea()->getDeclaredMeldList();
    }

    function getAllTileSortedList() {
        $sortedList = new TileSortedList($this->getHandTileSortedList()->toArray());
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