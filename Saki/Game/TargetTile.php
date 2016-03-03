<?php
namespace Saki\Game;

use Saki\Tile\Tile;

class TargetTile {
    private $tile;
    private $isKingSTile;
    private $isRobbingQuadTile;

    function __construct(Tile $tile, bool $isKingSTile = false, bool $isRobbingQuadTile = false) {
        $this->tile = $tile;
        $this->isKingSTile = $isKingSTile;
        $this->isRobbingQuadTile = $isRobbingQuadTile;
    }

    function getTile() {
        return $this->tile;
    }

    function isKingSTile() {
        return $this->isKingSTile;
    }

    function isRobbingQuadTile() {
        return $this->isRobbingQuadTile;
    }
}