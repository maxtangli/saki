<?php
namespace Saki\FinalScore;

use Saki\Game\Player;
use Saki\Game\PlayerList;

class FinalScoreStrategyTarget {

    /**
     * @var Player[]
     */
    private $players;

    function __construct(PlayerList $playerList) {
        $players = $playerList->toArray();
        usort($players, function (Player $pa, Player $pb) {
            if ($pa->getScore() != $pb->getScore()) {
                $ret = $pa->getScore() > $pb->getScore() ? 1 : -1;
            } else {
                $ret = $pa->getNo() < $pb->getNo() ? 1 : -1;
            }
            return -$ret; // desc sort
        });
        $this->players = array_values($players);
    }

    function getLastRoundScore($player) {
        return $player->getScore(); // assert no score changes
    }

    /**
     * @param $player
     * @return int 1|2|3|4 if same score, smaller index means better ranking.
     */
    function getLastRoundScoreRanking($player) {
        return array_search($player, $this->players) + 1;
    }

    function getPlayerCount() {
        return count($this->players);
    }

    function getPlayers() {
        return $this->players;
    }
}