<?php
namespace Saki\Game\Wall;
use Saki\Game\Tile\Tile;

/**
 * @package Saki\Game\Wall
 */
class ReplacementWall extends LiveWall {
    function __construct() {
        parent::__construct(false);
    }

    /**
     * @return Tile
     */
    function drawReplacement() {
        return $this->outNext();
    }

    function ableDrawReplacement() {
        return $this->ableOutNext();
    }
}