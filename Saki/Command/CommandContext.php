<?php
namespace Saki\Command;

use Saki\Game\Round;
use Saki\Game\RoundData;

class CommandContext {
    private $roundData;

    function __construct(RoundData $roundData) {
        $this->roundData = $roundData;
    }

    function getRoundData() {
        return $this->roundData;
    }
}