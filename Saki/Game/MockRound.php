<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

class MockRound extends Round {

    function debugSetTurn(Player $currentPlayer, $isPublicPhase, $globalTurn) {
        $roundPhase = RoundPhase::getInstance($isPublicPhase ? RoundPhase::PUBLIC_PHASE : RoundPhase::PRIVATE_PHASE);

        $this->getRoundData()->getTurnManager()->debugSet($currentPlayer, $roundPhase, $globalTurn);
    }

    function debugSetRoundWindData(Tile $roundWind) {
        $this->getRoundData()->getRoundWindData()->setRoundWind($roundWind);
    }

    function debugSetWallPopTile(Tile $tile) {
        $wallRemainTileList = $this->getRoundData()->getTileAreas()->getWall()->getRemainTileList();
        $wallRemainTileList->replaceByIndex($wallRemainTileList->count() - 1, $tile);
    }

    function debugSetHandTileList(Player $player, TileList $replacedHandTileList) {
        $tileAreas = $this->getRoundData()->getTileAreas();
        $tileAreas->debugSet($player, $replacedHandTileList, $player->getTileArea()->getDeclaredMeldList(), $tileAreas->getTargetTile());
    }

    function debugDiscardByReplace(Player $player, Tile $selfTile, TileList $replacedHandTileList = null) {
        $this->debugByReplaceImpl($player, $selfTile, $replacedHandTileList);
        $this->discard($player, $selfTile);
    }

    function debugReachByReplace(Player $player, Tile $selfTile, TileList $replacedHandTileList = null) {
        $this->debugByReplaceImpl($player, $selfTile, $replacedHandTileList);
        $this->reach($player, $selfTile);
    }

    protected function debugByReplaceImpl(Player $player, Tile $selfTile, TileList $replacedHandTileList = null) {
        if ($replacedHandTileList) {
            if (!$replacedHandTileList->valueExist($selfTile)) {
                throw new \InvalidArgumentException();
            }
            $this->debugSetHandTileList($player, $replacedHandTileList);
        } else {
            $handTileList = $player->getTileArea()->get13styleHandTileList();
            $handTileList->replaceByIndex(0, $selfTile);
            $this->debugSetHandTileList($player, $handTileList);
        }
    }

    function debugKongBySelfByReplace(Player $player, Tile $selfTile) {
        $handTileList = $player->getTileArea()->get13styleHandTileList();
        $handTileList->replaceByIndex([0, 1, 2, 3], [$selfTile, $selfTile, $selfTile, $selfTile]);
        $this->debugSetHandTileList($player, $handTileList);
        $this->kongBySelf($player, $selfTile);
    }
}