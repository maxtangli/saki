<?php
namespace Saki\Command;

use Saki\Game\Areas;
use Saki\Game\Round;

/**
 * @package Saki\Command
 */
class CommandContext {
    private $round;

    /**
     * @param Round $round
     */
    function __construct(Round $round) {
        $this->round = $round;
    }

    /**
     * @return Round
     */
    function getRound() {
        return $this->round;
    }

    //region sugar methods todo remove after Round members rearranged 
    /**
     * @return Areas
     */
    function getAreas() {
        return $this->getRound()->getAreas();
    }
    //endregion
}