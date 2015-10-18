<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\ArrayLikeObject;
use Saki\Game\Player;

class PlayerList extends ArrayLikeObject {

    static function createStandard() {
        return new self(4, 25000);
    }

    /**
     * @var Player[]
     */
    private $players;
    private $currentIndex;

    private $globalTurn;

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

        $this->players = $players;
        $this->currentIndex = 0;
        $this->globalTurn = 1;
    }

    function reset(Player $dealerPlayer) {
        $dealerIndex = $this->valueToIndex($dealerPlayer); // assert valid

        // roll each player's selfWind by its offset to dealerPlayer
        $dealerSelfWind = Tile::fromString('E');
        foreach($this->players as $index => $player) {
            $offsetToDealer = $index - $dealerIndex;
            $playerSelfWind = $dealerSelfWind->toNextTile($offsetToDealer);
            $player->reset($playerSelfWind);
        }

        $this->currentIndex = $dealerIndex;
        $this->globalTurn = 1;
    }

    function getGlobalTurn() {
        return $this->globalTurn;
    }

    /**
     * @return Player[]
     */
    function getTopPlayers() {
        $topPlayers = [];
        $topScore = 0;
        foreach ($this as $player) {
            if ($player->getScore() >= $topScore) {
                if ($player->getScore() > $topScore) {
                    $topPlayers = [$player];
                } else {
                    $topPlayers[] = $player;
                }
                $topScore = $player->getScore();
            }
        }
        return $topPlayers;
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
        return $this->players[$this->currentIndex];
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

    function hasMinusScorePlayer() {
        return $this->any(function (Player $player) {
            return $player->getScore() < 0;
        });
    }

    /**
     * @param Player $player
     */
    function toPlayer(Player $player) {
        $targetIndex = $this->valueToIndex($player); // valid check
        $addTurn = ($targetIndex < $this->currentIndex);
        $this->currentIndex = $targetIndex;
        if ($addTurn) {
            ++$this->globalTurn;
        }
    }

    function toNextPlayer() {
        $this->toPlayer($this->getNextPlayer());
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