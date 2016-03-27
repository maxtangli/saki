<?php

namespace Saki\Tile;

use Saki\Util\ArrayLikeObject;
use Saki\Util\Factory;
use Saki\Util\ValueObject;

class Tile implements ValueObject {
    const REGEX_SUIT_NUMBER = '[0-9]'; // 0 means red dora 5
    const REGEX_SUIT_TILE = '(' . self::REGEX_SUIT_NUMBER . TileType::REGEX_SUIT_TYPE . ')';
    const REGEX_HONOR_TILE = TileType::REGEX_HONOR_TYPE;
    const REGEX_TILE = '(' . self::REGEX_SUIT_TILE . '|' . self::REGEX_HONOR_TILE . ')';

    static function valid(TileType $tileType, $number, $isRedDora) {
        return ($tileType->isSuit() && self::validNumber($number) && ($isRedDora === false || $number === 5))
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

        // will be validated in getInstance()
        if (strlen($s) == 1) {
            return Tile::getInstance(TileType::fromString($s));
        } elseif (strlen($s) == 2) {
            if ($s[0] == '0') {
                return Tile::getInstance(TileType::fromString($s[1]), 5, true);
            } else {
                return Tile::getInstance(TileType::fromString($s[1]), intval($s[0]));
            }
        }

        throw new \LogicException();
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

    private static $valueIDBases = [
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

    /**
     * @param TileType $tileType
     * @param int|null $number
     * @param bool|false $isRedDora
     * @return int
     *
     * ValueID map
     *
     * - m 1-9   r5m  0
     * - p 11-19 r5p 10
     * - s 21-29 r5s 20
     * - E 31 S 32 W 33 N 34
     * - C 41 P 42 F 43
     */
    private static function toValueID(TileType $tileType, $number = null, $isRedDora = false) {
        $baseID = self::$valueIDBases[$tileType->getValue()];
        $numberID = $tileType->isSuit() ? ($isRedDora ? 0 : $number) : 0;
        return $baseID + $numberID;
    }

    /**
     * @param TileType $tileType
     * @param null $number
     * @param bool|false $isRedDora
     * @return int
     */
    private static function toDisplayValueID(TileType $tileType, $number = null, $isRedDora = false) {
        $ignoreRedDoraID = self::toValueID($tileType, $number, false);
        $finalID = $ignoreRedDoraID * 10 - ($isRedDora ? 1 : 0);
        return $finalID;
    }

    /**
     * A trick to support redDora while keep == operator ignores redDora(since == compares object members only).
     *
     * The trick exists because when red dora is considered to be added,
     * Tile comparisons with == have been used so much
     * that it's too expensive to add a $redDora member and replace tons of Tile == by Tile.equals(), which should be the common way.
     *
     * Note that WeakMap is not necessary since Tile is a Multiton Class.
     * @var Tile[]
     */
    private static $redDoraInstances = [];

    private static function isRedDoraTile(Tile $tile) {
        return in_array($tile, self::$redDoraInstances, true);
    }

    /**
     * @param TileType $tileType
     * @param null|int $number
     * @param bool $isRedDora
     * @return Tile
     */
    static function getInstance(TileType $tileType, $number = null, $isRedDora = false) {
        if (!self::valid($tileType, $number, $isRedDora)) {
            throw new \InvalidArgumentException(
                "Invalid argument \$tileType[$tileType], \$number[$number].Remind that \$number should be a int."
            );
        }

        $tileFactory = Factory::getInstance(__CLASS__);
        $key = self::toValueID($tileType, $number, $isRedDora);
        $generator = function () use ($tileType, $number, $isRedDora) {
            $obj = new Tile($tileType, $number, $isRedDora);
            if ($isRedDora) {
                self::$redDoraInstances[] = $obj;
            }
            return $obj;
        };
        return $tileFactory->getOrGenerate($key, $generator);
    }

    private $tileType;
    private $number;

    private function __construct(TileType $tileType, $number = null, $isRedDora = false) {
        // validated by getInstance()
        $this->tileType = $tileType;
        $this->number = $number;
    }

    function __toString() {
        $numberString = $this->isSuit() ? ($this->isRedDora() ? 0 : $this->getNumber()) : '';
        $typeString = $this->getTileType()->__toString();
        return sprintf('%s%s', $numberString, $typeString);
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

    function getValueID() {
        return self::toValueID($this->tileType, $this->number, $this->isRedDora());
    }

    function getDisplayValueID() {
        return self::toDisplayValueID($this->tileType, $this->number, $this->isRedDora());
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
        return $a->getNext($this, $offset);
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