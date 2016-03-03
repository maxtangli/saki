<?php
namespace Saki\Command;

use Saki\Game\Round;

class CommandContext {
    private $round;

    // todo remove Round
    function __construct(Round $round) {
        $this->round = $round;
    }

    function getRound() {
        return $this->round;
    }

    function getRoundData() {
        return $this->getRound()->getRoundData();
    }
}