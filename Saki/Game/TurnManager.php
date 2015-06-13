<?php
namespace Saki\Game;

class TurnManager {
    private $players;
    private $currentPlayerIndex; // NOTE: InfiniteIterator seems buggy with SESSION
    private $currentTurn;

    function __construct(array $players, $currentPlayer = null, $currentTurn = 1) {
        if (count($players) < 1) {
            throw new \InvalidArgumentException("Invalid empty \$players.");
        }

        if ($currentPlayer !== null) {
            $currentPlayerIndex = array_search($currentPlayer, $players, true);
            if ($currentPlayerIndex === false) {
                throw new \InvalidArgumentException();
            }
        } else {
            $currentPlayerIndex = 0;
        }

        $this->players = $players;
        $this->currentPlayerIndex = $currentPlayerIndex;
        $this->currentTurn = $currentTurn;
    }

    function getPlayers() {
        return $this->players;
    }

    function getCurrentPlayer() {
        return $this->getPlayers()[$this->currentPlayerIndex];
    }

    function getCurrentTurn() {
        return $this->currentTurn;
    }

    function validPlayer($player) {
        return array_search($player, $this->getPlayers(), true) !== false;
    }

    function toNextPlayer($addTurn = true) {
        $this->currentPlayerIndex = ($this->currentPlayerIndex + 1) % count($this->getPlayers());
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