<?php

namespace Saki\Game;

/**
 * @package Saki\Game
 */
class Dice {
    private $number;

    function __construct() {
        $this->number = 6;
    }

    /**
     * @return int
     */
    function getNumber() {
        return $this->number;
    }

    /**
     * @return int
     */
    function roll() {
        $this->number = mt_rand(1, 6);
        return $this->getNumber();
    }
}