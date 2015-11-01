<?php

namespace Saki\Tile;

use Saki\Util\ArrayLikeObject;
use Saki\Util\Utils;

class Tile {
    const REGEX_SUIT_NUMBER = '[1-9]';
    const REGEX_SUIT_TILE = '(' . self::REGEX_SUIT_NUMBER . TileType::REGEX_SUIT_TYPE . ')';
    const REGEX_HONOR_TILE = TileType::REGEX_HONOR_TYPE;
    const REGEX_TILE = '(' . self::REGEX_SUIT_TILE . '|' . self::REGEX_HONOR_TILE . ')';

    static function valid(TileType $tileType, $number, $isRedDora) {
        return ($tileType->isSuit() && self::validNumber($number) && ($isRedDora == false || $number == 5))
        || ($tileType->isHonor() && $number === null && $isRedDora == false);
    }

    static function validNumber($number) {
        return is_int($number) && 1 <= $number && $number <= 9;
    }

    static function validString($s) {
        $regex = '/^' . self::REGEX_TILE . '$/';
        return preg_match($regex, $s) === 1;
    }

    static function fromString($s) {
        if (!self::validString($s)) {
            throw new \InvalidArgumentException();
        }
        if (strlen($s) == 1) {
            return Tile::getInstance(TileType::fromString($s));
        } else {
            return Tile::getInstance(TileType::fromString($s[1]), intval($s[0]));
        }
    }

    static function getNumberTiles(TileType $tileType) {
        $a = new ArrayLikeObject(range(1, 9));
        return $a->toArray(function ($v) use ($tileType) {
            return Tile::getInstance($tileType, $v);
        });
    }

    static function getWindTiles($n = 4) {
        $valid = in_array(4, range(1, 4));
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $a = [Tile::fromString('E'), Tile::fromString('S'), Tile::fromString('W'), Tile::fromString('N')];
        return array_slice($a, 0, $n);
    }

    static function getDragonTiles() {
        return [Tile::fromString('C'), Tile::fromString('P'), Tile::fromString('F')];
    }

    private static $instances = []; // todo weakMap?
    private static $redDoraInstances = []; // todo weakMap?

    private static function isRedDoraTile(Tile $tile) {
        return in_array($tile, self::$redDoraInstances, true);
    }

    /**
     * @param TileType $tileType
     * @param int|null $number
     * @param bool|false $isRedDora
     * @return int
     *
     * - m 1-9   r5m  0
     * - p 11-19 r5p 10
     * - s 21-29 r5s 20
     * - E 31 S 32 W 33 N 34
     * - C 41 P 42 F 43
     */
    private static function toValueID(TileType $tileType, $number = null, $isRedDora = false) {
        $bases = [
            TileType::CHARACTER_M => 0,
            TileType::DOT_P => 10,
            TileType::BAMBOO_S => 20,

            TileType::EAST_E => 31,
            TileType::SOUTH_S => 32,
            TileType::WEST_W => 33,
            TileType::NORTH_N => 34,

            TileType::RED_C => 35,
            TileType::WHITE_P => 36,
            TileType::GREEN_F => 37,
        ];
        $baseID = $bases[$tileType->getValue()];
        $numberID = $tileType->isSuit() ? ($isRedDora ? 0 : $number) : 0;
        return $baseID + $numberID;
    }

    /**
     * @param TileType $tileType
     * @param null|int $number
     * @param bool $isRedDora
     * @return Tile
     */
    static function getInstance(TileType $tileType, $number = null, $isRedDora = false) {
        if (!self::valid($tileType, $number, $isRedDora)) {
            throw new \InvalidArgumentException("Invalid argument \$tileType[$tileType], \$number[$number].Remind that \$number should be a int.");
        }

        $key = self::toValueID($tileType, $number, $isRedDora);
        if (!isset(self::$instances[$key])) {
            $obj = new Tile($tileType, $number, $isRedDora);
            self::$instances[$key] = $obj;

            if ($isRedDora) {
                self::$redDoraInstances[] = $obj;
            }
        }
        return self::$instances[$key];
    }

    private $tileType;
    private $number;

    private function __construct(TileType $tileType, $number = null, $isRedDora = false) {
        // valid checked by getInstance()
        $this->tileType = $tileType;
        $this->number = $number;
    }

    function __toString() {
        $tileType = $this->getTileType();
        return $tileType->isSuit() ? $this->getNumber() . $tileType->__toString() : $tileType->__toString();
    }

    function getTileType() {
        return $this->tileType;
    }

    function getNumber() {
        if (!$this->tileType->isSuit()) {
            throw new \BadMethodCallException('getNumber() is not supported on non-suit tile.');
        }
        return $this->number;
    }

    function isRedDora() {
        return $this->isRedDoraTile($this);
    }

    function isSuit() {
        return $this->getTileType()->isSuit();
    }

    function isHonor() {
        return $this->getTileType()->isHonor();
    }

    function isWind() {
        return $this->getTileType()->isWind();
    }

    function isDragon() {
        return $this->getTileType()->isDragon();
    }

    function isSimple() {
        return $this->isSuit() && !in_array($this->getNumber(), [1, 9]);
    }

    function isTerminal() {
        return $this->isSuit() && in_array($this->getNumber(), [1, 9]);
    }

    function isTerminalOrHonor() {
        return $this->isTerminal() || $this->isHonor();
    }

    /**
     * @param int $offset
     * @return Tile
     */
    function toNextTile($offset = 1) {
        $currentType = $this->getTileType();
        if ($currentType->isSuit()) {
            $a = self::getNumberTiles($currentType);
        } elseif ($currentType->isWind()) {
            $a = self::getWindTiles();
        } elseif ($currentType->isDragon()) {
            $a = self::getDragonTiles();
        } else {
            throw new \LogicException();
        }

        $a = new ArrayLikeObject($a);
        $next = $a->getNext($this, $offset);
        return $next;
    }

    function getWindOffset(Tile $other) {
        $valid = $this->isWind() && $other->isWind();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $windArray = new ArrayLikeObject(Tile::getWindTiles());
        $iThis = $windArray->valueToIndex($this);
        $iOther = $windArray->valueToIndex($other);
        return $iThis - $iOther;
    }
}