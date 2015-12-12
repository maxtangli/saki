<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

class MockRound extends Round {
    // modify
    function debugSetWallPopTile(Tile $tile) {
        $this->getRoundData()->getTileAreas()->getWall()->debugSetNextDrawTile($tile);
    }

    // modify
    function debugSetHand(Player $player, TileList $replaceHandTileList) {
        $this->getRoundData()->getTileAreas()->debugReplaceHand($player, $replaceHandTileList);
        // todo seems not right
    }

    // command
    function debugSkipTo(Player $currentPlayer, RoundPhase $roundPhase = null, $globalTurn = null,
                         Tile $mockDiscardTile = null) {
        $validCurrentState = $this->getRoundPhase()->isPrivateOrPublic();
        if (!$validCurrentState) {
            throw new \InvalidArgumentException();
        }

        $actualRoundPhase = $roundPhase ?: RoundPhase::getPrivatePhaseInstance();
        $validRoundPhase = $actualRoundPhase->isPrivateOrPublic();
        if (!$validRoundPhase) {
            throw new \InvalidArgumentException();
        }

        $actualGlobalTurn = $globalTurn ?: 1;
        $validActualGlobalTurn = $actualGlobalTurn == 1;
        if (!$validActualGlobalTurn) {
            throw new \InvalidArgumentException('Not implemented.');
        }

        $actualMockDiscardTile = $mockDiscardTile ?: Tile::fromString('C');
        $validMockDiscardTile = !$actualMockDiscardTile->isWind();
        if (!$validMockDiscardTile) {
            throw new \InvalidArgumentException('Not implemented: consider FourWindDiscardedDraw issue.');
        }

        $isTargetTurn = function () use ($currentPlayer, $actualRoundPhase) {
            $isTargetTurn = ($this->getCurrentPlayer() == $currentPlayer) && ($this->getRoundPhase() == $actualRoundPhase);
            return $this->isGameOver() || $isTargetTurn;
        };
        while (!$isTargetTurn()) {
            if ($this->getRoundPhase()->isPrivate()) {
                $this->debugDiscardByReplace($this->getCurrentPlayer(), $actualMockDiscardTile);
            } elseif ($this->getRoundPhase()->isPublic()) {
                $this->passPublicPhase();
            } else {
                throw new \LogicException();
            }
        }

        if ($this->getRoundData()->getTurnManager()->getGlobalTurn() != 1) {
            throw new \LogicException('Not implemented.');
        }
    }

    // command
    function debugDiscardByReplace(Player $player, Tile $discardTile, TileList $replaceHandTileList = null) {
        $actualReplaceHandTileList = $replaceHandTileList ?: new TileList([$discardTile]);
        $this->getRoundData()->getTileAreas()->debugReplaceHand($player, $actualReplaceHandTileList);

        $this->discard($player, $discardTile);
    }

    // command
    function debugReachByReplace(Player $player, Tile $reachTile, TileList $replaceHandTileList = null) {
        $actualReplaceHandTileList = $replaceHandTileList ?: new TileList([$reachTile]);
        $this->getRoundData()->getTileAreas()->debugReplaceHand($player, $actualReplaceHandTileList);

        $this->reach($player, $reachTile);
    }

    // command
    function debugKongBySelfByReplace(Player $player, Tile $selfTile) {
        $this->getRoundData()->getTileAreas()->debugReplaceHand($player, new TileList([$selfTile, $selfTile, $selfTile, $selfTile]));
        $this->kongBySelf($player, $selfTile);
    }
}