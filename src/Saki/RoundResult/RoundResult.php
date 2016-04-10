<?php
namespace Saki\RoundResult;

use Saki\Game\Player;

abstract class RoundResult {
    /**
     * @var Player[]
     */
    private $players;
    private $originPoints;
    private $roundResultType;

    function __construct(array $players, RoundResultType $roundResultType) {
        if (count($players) != 4) {
            throw new \InvalidArgumentException();
        }
        $this->players = $players;
        $this->roundResultType = $roundResultType;
        $this->originPoints = array_map(function (Player $player) {
            return $player->getArea()->getPoint();
        }, $players);
    }

    function getPlayers() {
        return $this->players;
    }

    function getRoundResultType() {
        return $this->roundResultType;
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