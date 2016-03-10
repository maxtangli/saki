<?php

namespace Saki\Game;

use Saki\Command\CommandContext;
use Saki\Command\Debug\PassAllCommand;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\PrivateCommand\ConcealedKongCommand;
use Saki\Command\PrivateCommand\ReachCommand;
use Saki\Command\PrivateCommand\WinBySelfCommand;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class Round {
    private $roundData;

    function __construct(RoundData $roundData = null) {
        $actualRoundData = $roundData ?? new RoundData();
        $this->roundData = $actualRoundData; // nullPhase now
        $this->getRoundData()->toNextPhase(); // initPhase now
        $this->getRoundData()->toNextPhase(); // privatePhase now
    }

    // command
    function debugDiscardByReplace(Player $player, Tile $discardTile, TileList $replaceHandTileList = null) {
        $actualReplaceHandTileList = $replaceHandTileList ?? new TileList([$discardTile]);
        $this->getRoundData()->getTileAreas()->debugReplaceHand($player, $actualReplaceHandTileList);

        $this->discard($player, $discardTile);
    }

    // command
    function debugKongBySelfByReplace(Player $player, Tile $selfTile) {
        $this->getRoundData()->getTileAreas()->debugReplaceHand($player, new TileList([$selfTile, $selfTile, $selfTile, $selfTile]));
//        $this->kongBySelf($player, $selfTile);
        (new ConcealedKongCommand(new CommandContext($this->getRoundData()), $player->getSelfWind(), $selfTile))->execute();
    }

    /**
     * @return RoundData todo
     */
    function getRoundData() {
        return $this->roundData;
    }

    /**
     * @return RoundPhase todo
     */
    function getRoundPhase() {
//        return $this->getRoundData()->getTurnManager()->getRoundPhase();
        return $this->getRoundData()->getPhaseState()->getRoundPhase();
    }

    /**
     * @return Player todo
     */
    function getCurrentPlayer() {
        return $this->getRoundData()->getTurnManager()->getCurrentPlayer();
    }

    /**
     * @return PlayerList ok: already same method in RoundData
     */
    function getPlayerList() {
        return $this->getRoundData()->getPlayerList();
    }

    // ok: already same method in RoundData
    function getWinResult(Player $player) {
        return $this->getRoundData()->getWinResult($player);
    }

    // todo remove
    function discard(Player $player, Tile $selfTile) {
        (new DiscardCommand(new CommandContext($this->getRoundData()), $player->getSelfWind(), $selfTile))->execute();
    }

    // todo remove
    function passPublicPhase() {
        (new PassAllCommand(new CommandContext($this->getRoundData())))->execute();
    }
}