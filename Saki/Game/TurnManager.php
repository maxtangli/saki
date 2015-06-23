<?php
namespace Saki\Game;

class TurnManager {
    /**
     * @var TurnManagerItem[]
     */
    private $items;
    private $currentIndex;

    function __construct(array $players, $currentPlayer, $currentTurn) {
        if (count($players) < 1) {
            throw new \InvalidArgumentException("Invalid empty \$players.");
        }
        $this->items = array_map(function($p){return new TurnManagerItem($p,0);}, array_values($players));
        $this->currentIndex = 0;

        $this->toPlayer($currentPlayer, false);
        $this->getCurrentItem()->setTurn($currentTurn);
    }

    function getPlayers() {
        return array_map(function($item){return $item->getPlayer();}, $this->items);
    }

    function getPlayerCount() {
        return count($this->getPlayers());
    }

    protected function getCurrentItem() {
        return $this->items[$this->currentIndex];
    }

    function getCurrentPlayer() {
        return $this->getCurrentItem()->getPlayer();
    }

    function getNextPlayer() {
        $nextIndex = ($this->currentIndex + 1) % $this->getPlayerCount();
        return $this->items[$nextIndex]->getPlayer();
    }

    function toPlayer($player, $addTurn = true) {
        list($i, $item) = $this->find($player);
        $this->currentIndex = $i;
        if ($addTurn) {
            $item->addTurn();
        }
    }

    protected function find($player) {
        foreach ($this->items as $i => $item) {
            if ($item->getPlayer() == $player) {
                return [$i, $item];
            }
        }
        throw new \InvalidArgumentException();
    }

    function toNextPlayer($addTurn = true) {
        $this->toPlayer($this->getNextPlayer(), $addTurn);
    }

    function getPlayerTurn($player) {
        list($i, $item) = $this->find($player);
        return $item->getTurn();
    }

    function getCurrentPlayerTurn() {
        return $this->getCurrentItem()->getTurn();
    }

    function getTotalTurn() {
        return array_sum(array_map(function($p){return $p->getTurn();}, $this->items));
    }
}

class TurnManagerItem {
    private $player;
    private $turn;

    function __construct($player, $turn) {
        $this->player = $player;
        $this->turn = $turn;
    }

    function __toString() {
        return sprintf('player %s at turn %s', $this->getPlayer(), $this->getTurn());
    }

    function getPlayer() {
        return $this->player;
    }

    function getTurn() {
        return $this->turn;
    }

    function setTurn($turn) {
        $this->turn = $turn;
    }

    function addTurn() {
        ++$this->turn;
    }
}