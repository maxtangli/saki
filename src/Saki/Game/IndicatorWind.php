<?php
namespace Saki\Game;

use Saki\Game\Tile\Tile;
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
    private static $m = ['E' => 1, 'S' => 2, 'W' => 3, 'N' => 4];

    static function fromIndex(int $index) {
        $s = array_flip(self::$m)[$index];
        return static::fromString($s);
    }

    function getIndex() {
        return self::$m[$this->__toString()];
    }

    function toNext(int $offset = 1) {
        $nextWind = $this->getWindTile()->getNextTile($offset);
        return static::fromString($nextWind->__toString());
    }
    //endregion

    /**
     * @param int $offset
     * @return static
     */
    function toPrev(int $offset = 1) {
        $windCount = 4;
        $nextOffset = $windCount - $offset;
        return $this->toNext($nextOffset);
    }

    private static $instances = [];

    /**
     * @param string $s
     * @return static
     */
    static function fromString(string $s) {
        $class = get_called_class();
        self::$instances[$class][$s] = self::$instances[$class][$s] ?? new static(Tile::fromString($s));
        return self::$instances[$class][$s];
    }

    /**
     * @return static
     */
    static function createEast() {
        return static::fromString('E');
    }

    /**
     * @return static
     */
    static function createSouth() {
        return static::fromString('S');
    }

    /**
     * @return static
     */
    static function createWest() {
        return static::fromString('W');
    }

    /**
     * @return static
     */
    static function createNorth() {
        return static::fromString('N');
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
    protected function __construct(Tile $wind) {
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