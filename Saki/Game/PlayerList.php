<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\ArrayLikeObject;

class PlayerList extends ArrayLikeObject {

    static function createStandard() {
        return new self(4, 25000);
    }

    private $currentIndex;
    private $items;

    /**
     * @param int $n
     * @param int $initialScore
     */
    function __construct($n, $initialScore) {
        if ($n != 4) {
            throw new \InvalidArgumentException('Invalid player count.');
        }
        $data = [
            [1, $initialScore, Tile::fromString('E')],
            [2, $initialScore, Tile::fromString('S')],
            [3, $initialScore, Tile::fromString('W')],
            [4, $initialScore, Tile::fromString('N')],
        ];
        $players = array_map(function ($v) {
            return new Player($v[0], $v[1], $v[2]);
        }, array_slice($data, 0, $n));

        parent::__construct($players);
        $this->currentIndex = 0;
        $this->items = $players;
    }

    function reset(Player $dealerPlayer) {
        if (!$this->valueExist($dealerPlayer)) {
            throw new \InvalidArgumentException();
        }
        $selfWinds = [
            Tile::fromString('E'), Tile::fromString('S'), Tile::fromString('W'), Tile::fromString('N'),
        ];
        $count = $this->count();
        for ($offset = 0; $offset < $count; ++$offset) {
            $this->getOffsetPlayer($offset, $dealerPlayer)->reset($selfWinds[$offset]);
        }
        $this->toPlayer($dealerPlayer, false);
    }

    /**
     * @return Player
     */
    function getTopPlayer() {
        $topPlayer = $this[0];
        foreach($this as $player) {
            if ($player->getScore() > $topPlayer->getScore()) {
                $topPlayer = $player;
            }
        }
        return $topPlayer;
    }

    /**
     * @return Player
     */
    function getPrevPlayer() {
        return $this->getCurrentOffsetPlayer(-1);
    }

    /**
     * @return Player
     */
    function getCurrentPlayer() {
        return $this->items[$this->currentIndex];
    }

    /**
     * @return Player
     */
    function getNextPlayer() {
        return $this->getCurrentOffsetPlayer(1);
    }

    /**
     * @param int $offset
     * @return Player
     */
    function getCurrentOffsetPlayer($offset) {
        return $this->getOffsetPlayer($offset, $this->getCurrentPlayer());
    }

    /**
     * @param int $offset
     * @return Player
     */
    function getDealerOffsetPlayer($offset) {
        return $this->getOffsetPlayer($offset, $this->getDealerPlayer());
    }

    /**
     * @param int $offset
     * @param Player $basePlayer
     * @return Player
     */
    function getOffsetPlayer($offset, Player $basePlayer) {
        $baseIndex = $this->valueToIndex($basePlayer);
        $i = ($baseIndex + $offset + $this->count()) % $this->count();
        return $this[$i];
    }

    /**
     * @return Player
     */
    function getDealerPlayer() {
        $result = [];
        foreach ($this as $player) {
            if ($player->isDealer()) {
                $result[] = $player;
            }
        }
        if (count($result) != 1) {
            throw new \LogicException('not one and only one dealer.');
        }
        return $result[0];
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