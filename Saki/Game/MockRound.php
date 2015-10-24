<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

class MockRound extends Round {

    function debugSetTurn(Player $currentPlayer, $isPublicPhase, $globalTurn) {
        $this->getPlayerList()->toPlayer($currentPlayer);
        $roundPhase = RoundPhase::getInstance($isPublicPhase ? RoundPhase::PUBLIC_PHASE : RoundPhase::PRIVATE_PHASE);
        $this->getRoundData()->getTurnManager()->debugSetRoundPhase($roundPhase);
        $this->getPlayerList()->setGlobalTurn($globalTurn);
    }

    function debugSetRoundWindData(Tile $roundWind) {
        $this->getRoundData()->getRoundWindData()->setRoundWind($roundWind);
    }

    function debugSetTargetTile(Tile $targetTile) {
        $this->getRoundData()->getTileAreas()->setTargetTile($targetTile);
    }

    function debugSetWallPopTile(Tile $tile) {
        $wallRemainTileList = $this->getRoundData()->getTileAreas()->getWall()->getRemainTileList();
        $wallRemainTileList->replaceByIndex($wallRemainTileList->count() - 1, $tile);
    }

    function debugSetHandTileList(Player $player, TileList $replacedHandTileList) {
        $player->getPlayerArea()->getHandTileSortedList()->setInnerArray($replacedHandTileList->toArray());
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
            $player->getPlayerArea()->getHandTileSortedList()->replaceByIndex(0, $selfTile);
        }
    }

    function debugKongBySelfByReplace(Player $player, Tile $selfTile) {
        $player->getPlayerArea()->getHandTileSortedList()->replaceByIndex([0, 1, 2, 3], [$selfTile, $selfTile, $selfTile, $selfTile]);
        $this->kongBySelf($player, $selfTile);
    }
}