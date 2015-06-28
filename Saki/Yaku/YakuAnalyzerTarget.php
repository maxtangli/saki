<?php
namespace Saki\Yaku;

use Saki\Game\Player;
use Saki\Game\PlayerArea;
use Saki\Tile;
use Saki\TileSortedList;

class YakuAnalyzerTarget {
    private $playerArea;
    private $player;

    function __construct(PlayerArea $playerArea, Player $player) {
        $this->playerArea = $playerArea;
        $this->player = $player;
    }

    function getHandTileSortedList() {
        return $this->playerArea->getHandTileSortedList();
    }

    function getDiscardedTileList() {
        return $this->playerArea->getDiscardedTileList();;
    }

    function getDeclaredMeldList() {
        return $this->playerArea->getDeclaredMeldList();
    }

    function getAllTileSortedList() {
        $sortedList = new TileSortedList($this->getHandTileSortedList()->toArray());
        foreach($this->getDeclaredMeldList() as $meld) {
            $sortedList->push($meld->toArray());
        }
        return $sortedList;
    }

    function getWinTile() {
        return $this->playerArea->getCandidateTile();
    }

    function isReach() {
        return $this->playerArea->isReach();
    }

    function getSelfWind() {
        return $this->player->getSelfWind();
    }

    function getRoundWind() {
        return Tile::fromString('E'); // todo
    }
}