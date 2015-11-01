<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\Utils;

/**
 * History of chow, pong, kong declarations.
 * @package Saki\Game
 */
class DeclareHistory {

    /**
     * @var Tile[][]
     */
    private $a;

    function __construct() {
        $this->a = [];
    }

    function reset() {
        $this->a = [];
    }

    function recordDeclare($currentGlobalTurn, Tile $mySelfWind) {
        // todo assert valid

        $this->a[$currentGlobalTurn][] = $mySelfWind;
    }

    /**
     * @param $fromGlobalTurn
     * @param Tile $fromSelfWind
     * @return bool any declare exist since $fromGlobalTurn, $fromSelfWind
     */
    function hasDeclare($fromGlobalTurn, Tile $fromSelfWind) {
        // todo assert valid
        if (!isset($this->a[$fromGlobalTurn])) {
            return false;
        }

        foreach($this->a[$fromGlobalTurn] as $selfWind) {
            if ($selfWind->getWindOffset($fromSelfWind) >= 0) {
                return true;
            }
        }

        return false;
    }
}