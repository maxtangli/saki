<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\ArrayList;
use Saki\Util\ComparableIndex;
use Saki\Util\Immutable;

/**
 * Base class for PrevailingWind, SeatWind.
 * @package Saki\Game
 */
abstract class IndicatorWind implements Immutable {
    //region ComparableIndex Impl
    use ComparableIndex;

    static function fromIndex(int $index) {
        // todo safe check, remove duplicate
        $m = [
            'E' => 1,
            'S' => 2,
            'W' => 3,
            'N' => 4
        ];
        $s = array_flip($m)[$index];
        return static::fromString($s);
    }

    function getIndex() {
        $m = [
            'E' => 1,
            'S' => 2,
            'W' => 3,
            'N' => 4
        ];
        return $m[$this->__toString()];
    }

    function toNext(int $offset = 1) {
        return new static($this->getWindTile()->getNextTile($offset));
    }
    //endregion

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

    /**
     * @param int $n
     * @return ArrayList Return an ArrayList of static with $n values start from static::createEast();
     */
    static function createList(int $n) {
        return (new ArrayList(range(1, $n)))->select(function (int $index) {
            return static::fromIndex($index);
        });
    }

    private $wind;

    /**
     * @param Tile $wind
     */
    function __construct(Tile $wind) { // todo multiton pattern
        if (!$wind->isWind()) {
            throw new \InvalidArgumentException();
        }
        $this->wind = $wind;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getWindTile()->__toString();
    }

    /**
     * @return Tile
     */
    function getWindTile() {
        return $this->wind;
    }
}