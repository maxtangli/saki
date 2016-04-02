<?php
namespace Saki\Command;

use Saki\Game\Round;

class CommandContext {
    private $round;

    function __construct(Round $round) {
        $this->round = $round;
    }

    function getRound() {
        return $this->round;
    }
}