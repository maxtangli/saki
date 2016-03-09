<?php

namespace Saki\Game;

use Saki\Command\CommandContext;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\PrivateCommand\ExposedKongCommand;
use Saki\Command\PrivateCommand\NineNineDrawCommand;
use Saki\Command\PrivateCommand\PlusKongCommand;
use Saki\Command\PrivateCommand\ReachCommand;
use Saki\Command\PrivateCommand\WinBySelfCommand;
use Saki\Command\PublicCommand\BigKongCommand;
use Saki\Command\PublicCommand\ChowCommand;
use Saki\Command\PublicCommand\Debug\DebugPassCommand;
use Saki\Command\PublicCommand\PongCommand;
use Saki\Command\PublicCommand\SmallKongCommand;
use Saki\Command\PublicCommand\WinByOtherCommand;
use Saki\RoundPhase\OverPhaseState;
use Saki\RoundPhase\PrivatePhaseState;
use Saki\RoundResult\WinRoundResult;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayLikeObject;

/*

## note: round logic

new phase

- reset and shuffle wall
- decide dealer player
- decide each player's wind

reset phase

- each player draw 4 tiles
- goto dealer player's private phase

p's private phase: before execute command

- when enter: turn++, draw 1 tile if allowed
- show candidate commands
- always: discard one of onHand tile
- sometime: kong, plusKong, zimo

p's private phase: after execute command

- if discard: go to public phase
- if zimo: go to round-over phase
- if kong/plusKong: drawBack, stay in private phase

p's public phase: basic version

- public phase means waiting for other's response for current player's action
- poller responsible for select a final action for public phase

p's public phase: before execute command

- only non-current players may have candidate commands
- if none candidate commands exist: goto next player's private phase if remainTileCount > 0, otherwise go to over phase
- if candidate commands exist, wait for each player's response, and execute the highest priority ones.
- command types: ron, chow, pon, kong

p's public phase: after execute command

- if ron: go to round-over phase
- if chow/pon/kong: go to execute player's private phase?

over phase

- draw or win
- calculate points and modify players' points
- new next round

*/

class Round {
    private $roundData;

    function __construct(RoundData $roundData = null) {
        $actualRoundData = $roundData ?? new RoundData();
        $this->roundData = $actualRoundData; // nullPhase now
//        $this->getRoundData()->toInitPhase();
        $this->getRoundData()->toNextPhase(); // initPhase now
        $this->getRoundData()->toNextPhase(); // privatePhase now
    }

    // todo move into Command
    function debugSkipTo(Player $currentPlayer, RoundPhase $roundPhase = null, $globalTurn = null,
                         Tile $mockDiscardTile = null) {
        $validCurrentState = $this->getRoundPhase()->isPrivateOrPublic();
        if (!$validCurrentState) {
            throw new \InvalidArgumentException();
        }

        $actualRoundPhase = $roundPhase ?? RoundPhase::getPrivateInstance();
        $validRoundPhase = $actualRoundPhase->isPrivateOrPublic();
        if (!$validRoundPhase) {
            throw new \InvalidArgumentException();
        }

        $actualGlobalTurn = $globalTurn ?? 1;
        $validActualGlobalTurn = ($actualGlobalTurn == 1);
        if (!$validActualGlobalTurn) {
            throw new \InvalidArgumentException('Not implemented.');
        }

        $actualMockDiscardTile = $mockDiscardTile ?? Tile::fromString('C');
        $validMockDiscardTile = !$actualMockDiscardTile->isWind();
        if (!$validMockDiscardTile) {
            throw new \InvalidArgumentException('Not implemented: consider FourWindDiscardedDraw issue.');
        }

        $isTargetTurn = function () use ($currentPlayer, $actualRoundPhase) {
            $phaseState = $this->getRoundData()->getPhaseState();
            $isTargetTurn = ($this->getCurrentPlayer() == $currentPlayer) && ($phaseState->getRoundPhase() == $actualRoundPhase);
            $isGameOver = $phaseState->getRoundPhase()->isOver() && $phaseState->isGameOver($this->getRoundData());
            return $isGameOver || $isTargetTurn;
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
        $actualReplaceHandTileList = $replaceHandTileList ?? new TileList([$discardTile]);
        $this->getRoundData()->getTileAreas()->debugReplaceHand($player, $actualReplaceHandTileList);

        $this->discard($player, $discardTile);
    }

    // command
    function debugReachByReplace(Player $player, Tile $reachTile, TileList $replaceHandTileList = null) {
        $actualReplaceHandTileList = $replaceHandTileList ?? new TileList([$reachTile]);
        $this->getRoundData()->getTileAreas()->debugReplaceHand($player, $actualReplaceHandTileList);

        $this->reach($player, $reachTile);
    }

    // command
    function debugKongBySelfByReplace(Player $player, Tile $selfTile) {
        $this->getRoundData()->getTileAreas()->debugReplaceHand($player, new TileList([$selfTile, $selfTile, $selfTile, $selfTile]));
        $this->kongBySelf($player, $selfTile);
    }

    /**
     * @return RoundData
     */
    function getRoundData() {
        return $this->roundData;
    }

    /**
     * @return RoundPhase
     */
    function getRoundPhase() {
//        return $this->getRoundData()->getTurnManager()->getRoundPhase();
        return $this->getRoundData()->getPhaseState()->getRoundPhase();
    }

    /**
     * @return PlayerList
     */
    function getPlayerList() {
        return $this->getRoundData()->getPlayerList();
    }

    /**
     * @return Player
     */
    function getCurrentPlayer() {
        return $this->getRoundData()->getTurnManager()->getCurrentPlayer();
    }

    // todo remove
    function getWinResult(Player $player) {
        return $this->getRoundData()->getWinResult($player);
    }

    // todo remove
    function discard(Player $player, Tile $selfTile) {
        (new DiscardCommand(new CommandContext($this->getRoundData()), $player->getSelfWind(), $selfTile))->execute();
    }

    // todo remove
    function reach(Player $player, Tile $selfTile) {
        (new ReachCommand(new CommandContext($this->getRoundData()), $player->getSelfWind(), $selfTile))->execute();
    }

    // todo remove
    function kongBySelf(Player $player, Tile $selfTile) {
        (new ExposedKongCommand(new CommandContext($this->getRoundData()), $player->getSelfWind(), $selfTile))->execute();
    }

    // todo remove
    function winBySelf(Player $player) {
        (new WinBySelfCommand(new CommandContext($this->getRoundData()), $player->getSelfWind()))->execute();
    }

    // todo remove
    function passPublicPhase() {
        (new DebugPassCommand(new CommandContext($this->getRoundData())))->execute();
    }
}