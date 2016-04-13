<?php
namespace Saki\Result;

use Saki\Game\Player;

abstract class Result {
    /**
     * @var Player[]
     */
    private $players;
    private $originPoints;
    private $ResultType;

    function __construct(array $players, ResultType $ResultType) {
        if (count($players) != 4) {
            throw new \BadMethodCallException('todo');
        }
        $this->players = $players;
        $this->ResultType = $ResultType;
        $this->originPoints = array_map(function (Player $player) {
            return $player->getArea()->getPoint();
        }, $players);
    }

    function getPlayers() {
        return $this->players;
    }

    function getResultType() {
        return $this->ResultType;
    }

    private function getOriginPoint(Player $player) {
        $k = array_search($player, $this->players);
        if ($k === false) {
            throw new \InvalidArgumentException();
        }
        return $this->originPoints[$k];
    }

    protected function getOriginDealerPlayer() {
        foreach ($this->getPlayers() as $player) {
            if ($player->getArea()->getSeatWind()->isDealer()) {
                return $player;
            }
        }
        throw new \LogicException();
    }

    /**
     * @param Player $player
     * @return PointDelta
     */
    final function getPointDelta(Player $player) {
        return new PointDelta($this->getOriginPoint($player), $this->getPointDeltaInt($player));
    }

    /**
     * @param Player $player
     * @return int
     */
    abstract function getPointDeltaInt(Player $player);

    /**
     * @return bool
     */
    abstract function isKeepDealer();
}