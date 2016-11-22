<?php
namespace Saki\Game;

use Saki\Util\ArrayList;

/**
 * @package Saki\Game
 */
class DicePair {
    private $diceList;

    function __construct() {
        $this->diceList = new ArrayList([
            new Dice(), new Dice()
        ]);
    }

    /**
     * @return array
     */
    function getDices() {
        return $this->diceList->toArray();
    }

    /**
     * @return int
     */
    function getNumber() {
        return $this->diceList->getSum(function (Dice $dice) {
            return $dice->getNumber();
        });
    }

    function roll() {
        $this->diceList->walk(function (Dice $dice) {
            $dice->roll();
        });
    }
}