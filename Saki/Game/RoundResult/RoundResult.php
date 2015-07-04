<?php
namespace Saki\Game\RoundResult;

use Saki\Game\Player;

abstract class RoundResult {
    /**
     * @param Player $player
     * @return ScoreDelta
     */
    final function getScoreDelta(Player $player) {
        return new ScoreDelta($player->getScore(), $this->getScoreDeltaInt($player));
    }

    /**
     * @param Player $player
     * @return int
     */
    abstract function getScoreDeltaInt(Player $player);
}