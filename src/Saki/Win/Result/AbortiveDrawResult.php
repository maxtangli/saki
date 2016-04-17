<?php
namespace Saki\Win\Result;

use Saki\Game\Player;
use Saki\Game\PlayerType;
use Saki\Game\SeatWind;

class AbortiveDrawResult extends Result {
    function __construct(array $players, ResultType $drawType) {
        if (!$drawType->isAbortiveDraw()) {
            throw new \InvalidArgumentException();
        }

        parent::__construct($players, $drawType);
    }

    function getPointDeltaInt(Player $player) {
        return 0;
    }

    function isKeepDealer() {
        return true;
    }
}