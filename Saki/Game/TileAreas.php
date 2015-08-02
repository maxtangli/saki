<?php

namespace Saki\Game;

// operations upon a Wall and 2-4 PlayerArea
use Saki\Tile\Tile;

class TileAreas {
    private $wall;
    private $playerList;
    private $accumulatedReachCount; // 積み棒

    function __construct(Wall $wall, PlayerList $playerList) {
        $this->wall = $wall;
        $this->playerList = $playerList;
        $this->accumulatedReachCount = 0;
    }

    function getWall() {
        return $this->wall;
    }

    function getAccumulatedReachCount() {
        return $this->accumulatedReachCount;
    }

    function setAccumulatedReachCount($accumulatedReachCount) {
        $this->accumulatedReachCount = $accumulatedReachCount;
    }

    function addAccumulatedReachCount() {
        $this->setAccumulatedReachCount($this->getAccumulatedReachCount() + 1);
    }

    function drawInit(Player $player, $drawTileCount) {
        $player->getPlayerArea()->drawInit($this->getWall()->pop($drawTileCount));
    }

    function draw(Player $player) {
        $player->getPlayerArea()->draw($this->getWall()->pop());
    }

    function drawReplacement(Player $player) {
        $player->getPlayerArea()->draw($this->getWall()->shift());
    }

    function discard(Player $player, Tile $selfTile) {
        $player->getPlayerArea()->discard($selfTile);
    }

    function kongBySelf(Player $player, Tile $selfTile) {
        $player->getPlayerArea()->kongBySelf($selfTile);
        $this->drawReplacement($player);
    }

    function plusKongBySelf(Player $player, Tile $selfTile) {
        $player->getPlayerArea()->plusKongBySelf($selfTile);
        $this->drawReplacement($player);
    }

    function chowByOther(Player $actPlayer, Tile $tile1, Tile $tile2, Player $targetPlayer) {
        $this->assertNextPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getPlayerArea();
        $actPlayerArea = $actPlayer->getPlayerArea();

        $targetTile = $targetPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $actPlayerArea->chowByOther($targetTile, $tile1, $tile2); // test valid
        $targetPlayerArea->getDiscardedTileList()->pop();
    }

    function pongByOther(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getPlayerArea();
        $actPlayerArea = $actPlayer->getPlayerArea();

        $targetTile = $targetPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $actPlayerArea->pongByOther($targetTile); // test valid
        $targetPlayerArea->getDiscardedTileList()->pop();
    }

    function kongByOther(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getPlayerArea();
        $actPlayerArea = $actPlayer->getPlayerArea();

        $targetTile = $targetPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $actPlayerArea->kongByOther($targetTile); // test valid
        $this->drawReplacement($actPlayer);
        $targetPlayerArea->getDiscardedTileList()->pop();
    }

    function plusKongByOther(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $currentPlayerArea = $targetPlayer->getPlayerArea();
        $playerArea = $actPlayer->getPlayerArea();

        $targetTile = $currentPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $playerArea->plusKongByOther($targetTile);
        $this->drawReplacement($actPlayer);
        $currentPlayerArea->getDiscardedTileList()->pop();
    }

    protected function assertNextPlayer(Player $nextPlayer, Player $prePlayer) {
        list($iNext, $iPre) = $this->playerList->valueToIndex([$nextPlayer, $prePlayer]);
        $valid = ($iNext == ($iPre + 1));
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf('[%s] should be next of [%s]', $nextPlayer, $prePlayer)
            );
        }
    }

    protected function assertDifferentPlayer(Player $player, Player $otherPlayer) {
        $valid = $player != $otherPlayer;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
    }
}