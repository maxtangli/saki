<?php
namespace Saki\FinalPoint;

use Saki\Game\Player;
use Saki\Game\PlayerList;

class FinalPointStrategyTarget {
    /**
     * @var Player[]
     */
    private $players;

    function __construct(PlayerList $playerList) {
        $players = $playerList->toArray();
        usort($players, function (Player $pa, Player $pb) {
            if ($pa->getArea()->getPoint() != $pb->getArea()->getPoint()) {
                $ret = $pa->getArea()->getPoint() > $pb->getArea()->getPoint() ? 1 : -1;
            } else {
                $ret = $pa->getNo() < $pb->getNo() ? 1 : -1;
            }
            return -$ret; // desc sort
        });
        $this->players = array_values($players);
    }

    function getLastRoundPoint($player) {
        return $player->getArea()->getPoint(); // assert no point changes
    }

    /**
     * @param $player
     * @return int 1|2|3|4 if same point, smaller index means better ranking.
     */
    function getLastRoundPointRanking($player) {
        return array_search($player, $this->players) + 1;
    }

    function getPlayerCount() {
        return count($this->players);
    }

    function getPlayers() {
        return $this->players;
    }
}