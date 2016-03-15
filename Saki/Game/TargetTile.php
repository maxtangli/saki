<?php
namespace Saki\Game;

use Saki\Tile\Tile;

class TargetTile {
    private $tile;
    private $isKingSTile;
    private $isRobQuadTile;

    function __construct(Tile $tile, bool $isKingSTile = false, bool $isRobQuadTile = false) {
        $this->tile = $tile;
        $this->isKingSTile = $isKingSTile;
        $this->isRobQuadTile = $isRobQuadTile;
    }

    function getTile() {
        return $this->tile;
    }

    function isKingSTile() {
        return $this->isKingSTile;
    }

    function isRobQuadTile() {
        return $this->isRobQuadTile;
    }
}