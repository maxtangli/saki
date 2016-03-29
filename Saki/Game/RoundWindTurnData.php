<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\Utils;

class RoundWindTurnData {
    private $turn;

    function __construct($turn) {
        $this->assertValidTurn($turn);
        $this->turn = $turn;
    }

    protected function assertValidTurn($turn) {
        $valid = Utils::inRange($turn, 1, 4);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
    }

    function getTurn() {
        return $this->turn;
    }

    function setTurn($turn) {
        $this->assertValidTurn($turn);
        $this->turn = $turn;
    }

    function getDealerWind() {
        return Tile::fromString('E')->getNextTile($this->getTurn() - 1);
    }

    function setDealerWind(Tile $wind) {
        $turn = $wind->getWindOffset(Tile::fromString('E')); // validate
        $this->setTurn($turn);
    }
}