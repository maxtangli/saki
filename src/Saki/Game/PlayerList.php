<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\ArrayList;
use Saki\Util\ReadonlyArrayList;
use Saki\Util\Utils;

class PlayerList extends ArrayList {
    use ReadonlyArrayList;

    static function createStandard() {
        return new PlayerList(4, 25000);
    }

    /**
     * @var Player[]
     */
    private $players;

    /**
     * @param int $n
     * @param int $initialScore
     */
    function __construct(int $n, int $initialScore) {
        if (!Utils::inRange($n, 1, 4)) {
            throw new \InvalidArgumentException();
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
    }

    function hasMinusScorePlayer() {
        return $this->isAny(function (Player $player) {
            return $player->getScore() < 0;
        });
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
    function getDealerPlayer() {
        return $this->getSelfWindPlayer(Tile::fromString('E'));
    }

    function getSouthPlayer() {
        return $this->getSelfWindPlayer(Tile::fromString('S'));
    }

    function getWestPlayer() {
        return $this->getSelfWindPlayer(Tile::fromString('W'));
    }

    function getNorthPlayer() {
        return $this->getSelfWindPlayer(Tile::fromString('N'));
    }

    /**
     * @param Tile $selfWind
     * @return Player
     */
    function getSelfWindPlayer(Tile $selfWind) {
        $result = [];
        foreach ($this as $player) {
            /** @var Player $player */
            $player = $player;
            if ($player->getTileArea()->getPlayerWind()->getWindTile() == $selfWind) {
                $result[] = $player;
            }
        }
        if (count($result) != 1) {
            throw new \LogicException('not one and only one selfWind.');
        }
        return $result[0];
    }
}