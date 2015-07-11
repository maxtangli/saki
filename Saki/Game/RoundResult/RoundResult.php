<?php
namespace Saki\Game\RoundResult;

use Saki\Game\Player;

abstract class RoundResult {
    /**
     * @var Player[]
     */
    private $players;
    private $originScores;
    function __construct(array $players) {
        if (count($players) != 4) {
            throw new \InvalidArgumentException();
        }
        $this->players = $players;
        $this->originScores = array_map(function(Player $player){return $player->getScore();}, $players);
    }

    function getPlayers() {
        return $this->players;
    }

    private function getOriginScore(Player $player) {
        $k = array_search($player, $this->players);
        if ($k === false) {
            throw new \InvalidArgumentException();
        }
        return $this->originScores[$k];
    }

    protected function getOriginDealerPlayer() {
        foreach($this->getPlayers() as $player) {
            if ($player->isDealer()) {
                return $player;
            }
        }
        throw new \LogicException();
    }

    /**
     * @param Player $player
     * @return ScoreDelta
     */
    final function getScoreDelta(Player $player) {
        return new ScoreDelta($this->getOriginScore($player), $this->getScoreDeltaInt($player));
    }

    /**
     * @param Player $player
     * @return int
     */
    abstract function getScoreDeltaInt(Player $player);

    /**
     * @return Player
     */
    abstract function getNextDealerPlayer();
}