<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\Immutable;

/**
 * Base class for PrevailingWind, SeatWind.
 * @package Saki\Game
 */
abstract class IndicatorWind implements Immutable {
    /**
     * @param string $s
     * @return static
     */
    static function fromString(string $s) {
        return new static(
            Tile::fromString($s)
        );
    }

    /**
     * @return static
     */
    static function createEast() {
        return new static(Tile::fromString('E'));
    }

    /**
     * @return static
     */
    static function createSouth() {
        return new static(Tile::fromString('S'));
    }

    /**
     * @return static
     */
    static function createWest() {
        return new static(Tile::fromString('W'));
    }

    /**
     * @return static
     */
    static function createNorth() {
        return new static(Tile::fromString('N'));
    }

    private $wind;

    /**
     * @param Tile $wind
     */
    function __construct(Tile $wind) {
        $wind->assertWind();
        $this->wind = $wind;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getWindTile()->__toString();
    }

    /**
     * @param int $offset
     * @return SeatWind
     */
    function toNext(int $offset = 1) {
        return new static($this->getWindTile()->getNextTile($offset));
    }

    /**
     * @return Tile
     */
    function getWindTile() {
        return $this->wind;
    }

    /**
     * @return int
     */
    function getIndex() {
        $m = [
            'E' => 1,
            'S' => 2,
            'W' => 3,
            'N' => 4
        ];
        return $m[$this->__toString()];
    }
}