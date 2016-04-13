<?php
namespace Saki\Result;

use Saki\Game\Player;

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