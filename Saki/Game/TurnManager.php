<?php
namespace Saki\Game;

class TurnManager {
    private $players;
    private $currentPlayerIndex; // NOTE: do NOT use InfiniteIterator since it's buggy with SESSION
    private $currentTurn;

    function __construct(array $players, $currentPlayer = null, $currentTurn = 0) {
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

    function getCurrentTurn() {
        return $this->currentTurn;
    }

    function getPlayers() {
        return $this->players;
    }

    function getCurrentPlayer() {
        return $this->getPlayers()[$this->currentPlayerIndex];
    }

    function getNextPlayer() {
        return $this->getPlayers()[$this->getNextPlayerIndex()];
    }

    protected function getNextPlayerIndex() {
        return  ($this->currentPlayerIndex + 1) % count($this->getPlayers());
    }

    function validPlayer($player) {
        return array_search($player, $this->getPlayers(), true) !== false;
    }

    function toNextPlayer($addTurn = true) {
        $this->currentPlayerIndex = $this->getNextPlayerIndex();
        if ($addTurn) {
            ++$this->currentTurn;
        }
    }

    function toPlayer($player, $addTurn = true) {
        while ($this->getCurrentPlayer() !== $player) {
            $this->toNextPlayer(false);
        }
        if ($addTurn) {
            ++$this->currentTurn;
        }
    }
}