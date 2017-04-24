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
     * @return string
     */
    function __toString() {
        return sprintf("%s+%s", $this->diceList[0], $this->diceList[1]);
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

    /**
     * @return int
     */
    function roll() {
        $this->diceList->walk(function (Dice $dice) {
            $dice->roll();
        });
        return $this->getNumber();
    }
}