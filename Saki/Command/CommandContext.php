<?php
namespace Saki\Command;

use Saki\Game\Round;

class CommandContext {
    private $round;

    function __construct(Round $round) {
        $this->roundData = $round;
    }

    function getRoundData() {
        return $this->roundData;
    }
}