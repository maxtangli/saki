<?php
namespace Saki\RoundResult;

use Saki\Game\Player;

class OnTheWayDrawRoundResult extends RoundResult {

    function __construct(array $players, RoundResultType $drawType) {
        if (!$drawType->isOnTheWayDraw()) {
            throw new \InvalidArgumentException();
        }

        parent::__construct($players, $drawType);
    }

    function getScoreDeltaInt(Player $player) {
        return 0;
    }

    function isKeepDealer() {
        return true;
    }
}