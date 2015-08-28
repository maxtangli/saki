<?php
namespace Saki\Command;

use Saki\Game\Game;
use Saki\Game\Player;

class CommandFactory {
    private $game;

    function __construct(Game $game) {
        $this->game = $game;
    }

    protected function getGame() {
        return $this->game;
    }

    function getPlayer($playerString) {
        $game = $this->getGame();
        $playerStrings = array_map(function (Player $p) {
            return $p->__toString();
        }, $game->getCurrentRound()->getPlayerList()->toArray());
        $player = $game->getCurrentRound()->getPlayerList()[array_search($playerString, $playerStrings)];
        return $player;
    }

    function createCommand($commandString) { // discard p1 1m
        $commandTokens = explode(' ', $commandString);

        $commandClassString = $commandTokens[0]; // discard
        $commandClass = __NAMESPACE__ . '\\' . ucfirst($commandClassString) . 'Command'; // DiscardCommand

        $round = $this->getGame()->getCurrentRound();

        $playerString = $commandTokens[1];
        $player = $this->getPlayer($playerString);

        $remainCommandTokens = array_slice($commandTokens, 2);

        $command = $commandClass::fromString($round, $player, $remainCommandTokens);
        return $command;
    }
}