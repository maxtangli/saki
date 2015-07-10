<?php
namespace Saki\Win;

use Saki\Game\Player;
use Saki\Game\PlayerArea;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileSortedList;

class WinAnalyzerTarget {
    private $player;

    function __construct(Player $player) {
        $this->player = $player;
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
        return Tile::fromString('E'); // todo
    }

    function toSubTarget(MeldList $handMeldList) {
        return new WinAnalyzerSubTarget($handMeldList, $this->player);
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