<?php
namespace Saki\Game;

class TurnManager {
    private $players;
    private $currentTurn;

    private $playerInfiniteIterator;

    function __construct(array $players, $currentPlayer = null, $currentTurn = 1) {
        if (count($players) < 1) {
            throw new \InvalidArgumentException("Invalid empty \$players.");
        }

        $actualCurrentPlayer = $currentPlayer !== null ? $currentPlayer : $players[0];
        if (array_search($actualCurrentPlayer, $players, true) === false) {
            throw new \InvalidArgumentException("Invalid \$currentPlayer[$actualCurrentPlayer].");
        }

        $this->players = $players;
        $this->currentTurn = $currentTurn;

        $infiniteIterator = new \InfiniteIterator(new \ArrayIterator($players));
        while ($infiniteIterator->current() !== $actualCurrentPlayer) {
            $infiniteIterator->next();
        }
        $this->playerInfiniteIterator = $infiniteIterator;
    }

    function getPlayers() {
        return $this->players;
    }

    function getCurrentPlayer() {
        return $this->playerInfiniteIterator->current();
    }

    function getCurrentTurn() {
        return $this->currentTurn;
    }

    function validPlayer($player) {
        return array_search($player, $this->getPlayers(), true) !== false;
    }

    function toNextPlayer($addTurn = true) {
        $this->playerInfiniteIterator->next();
        if ($addTurn) {
            ++$this->currentTurn;
        }
    }

    function toPlayer($player, $addTurn = true) {
        while ($this->getCurrentPlayer() !== $player) {
            $this->toNextPlayer($addTurn);
        }
    }
}