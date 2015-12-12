<?php
namespace Saki\Game;

use Saki\Tile\Tile;

class TargetTileData {
    private $targetTile;
    private $roundPhase;

    function __construct(Tile $targetTile, RoundPhase $roundPhase) {
        $valid = $roundPhase->isPrivateOrPublic();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $this->targetTile = $targetTile;
        $this->roundPhase = $roundPhase;
    }

    function getTargetTile() {
        return $this->targetTile;
    }

    function getRoundPhase() {
        return $this->roundPhase;
    }
}