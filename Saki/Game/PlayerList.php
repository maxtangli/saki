<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\ArrayLikeObject;

class PlayerList extends ArrayLikeObject {

    static function getStandardPlayerList() {
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
        array_walk($this->items, function (Player $player) {
            $player->reset(Tile::fromString('E'));
        });
        $this->setDealerPlayer($dealerPlayer);
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
        return $this->getOffsetPlayer(-1);
    }

    /**
     * @return Player
     */
    function getCurrentPlayer() {
        return $this->getOffsetPlayer(0);
    }

    /**
     * @return Player
     */
    function getNextPlayer() {
        return $this->getOffsetPlayer(1);
    }

    /**
     * @param int $offset
     * @param Player $basePlayer
     * @return Player
     */
    function getOffsetPlayer($offset, Player $basePlayer = null) {
        $baseIndex = $basePlayer === null ? $this->currentIndex : $this->valueToIndex($basePlayer);
        $i = ($baseIndex + $offset + $this->count()) % $this->count();
        return $this[$i];
    }

    /**
     * @return Player
     */
    function getDealerPlayer() {
        foreach ($this as $player) {
            if ($player->isDealer()) {
                return $player;
            }
        }
        throw new \LogicException();
    }

    function setDealerPlayer(Player $player) {
        if (!$this->valueExist($player)) {
            throw new \InvalidArgumentException();
        }
        $selfWinds = [
            Tile::fromString('E'), Tile::fromString('S'), Tile::fromString('W'), Tile::fromString('N'),
        ];
        $playerCount = $this->count();
        for ($i = 0; $i < $playerCount; ++$i) {
            $this->getOffsetPlayer($i, $player)->setSelfWind($selfWinds[$i]);
        }
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