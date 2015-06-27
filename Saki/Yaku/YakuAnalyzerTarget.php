<?php
namespace Saki\Yaku;

use Saki\Game\Player;
use Saki\Game\PlayerArea;

class YakuAnalyzerTarget {
    private $playerArea;

    function __construct(PlayerArea $playerArea) {
        $this->playerArea = $playerArea;
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

    function getWinTile() {
        return $this->playerArea->getCandidateTile();
    }

    function isReach() {
        return $this->playerArea->isReach();
    }
}