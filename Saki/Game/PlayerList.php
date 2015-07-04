<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\ArrayLikeObject;

class PlayerList extends ArrayLikeObject {
    /**
     * @param int $n
     * @param int $initialScore
     * @return Player[]
     */
    static function createPlayers($n, $initialScore) {
        $data = [
            [1, $initialScore, Tile::fromString('E')],
            [2, $initialScore, Tile::fromString('S')],
            [3, $initialScore, Tile::fromString('W')],
            [4, $initialScore, Tile::fromString('N')],
        ];
        return array_map(function ($v) {
            return new Player($v[0], $v[1], $v[2]);
        }, array_slice($data, 0, $n));
    }

    private $currentIndex;
    private $items;

    /**
     * @param Player[] $players
     */
    function __construct(array $players) {
        parent::__construct($players);
        $this->currentIndex = 0;
        $this->items = $players;
    }

    /**
     * @return Player
     */
    function getPrevPrevPlayer() {
        return $this->getPlayer(-2);
    }

    /**
     * @return Player
     */
    function getPrevPlayer() {
        return $this->getPlayer(-1);
    }

    /**
     * @return Player
     */
    function getCurrentPlayer() {
        return $this->getPlayer(0);
    }

    /**
     * @return Player
     */
    function getNextPlayer() {
        return $this->getPlayer(1);
    }

    /**
     * @return Player
     */
    function getNextNextPlayer() {
        return $this->getPlayer(2);
    }

    /**
     * @return Player
     */
    function getPlayer($offset) {
        $i = ($this->currentIndex + $offset + $this->count()) % $this->count();
        return $this[$i];
    }

    /**
     * @param Player $player
     * @param bool $addTurn
     */
    function toPlayer(Player $player, $addTurn = true) {
        $this->currentIndex = $this->valueToIndex($player); // valid check
        if ($addTurn) {
            $player->addTurn();
        }
    }

    /**
     * @param bool $addTurn
     */
    function toNextPlayer($addTurn = true) {
        $this->toPlayer($this->getNextPlayer(), $addTurn);
    }

    /**
     * @return Player[]
     */
    function toArray() {
        return parent::toArray();
    }

    /**
     * @param int $offset
     * @return Player
     */
    function offsetGet($offset) {
        return parent::offsetGet($offset);
    }
}