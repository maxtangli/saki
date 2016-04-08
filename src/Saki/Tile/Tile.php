<?php

namespace Saki\Tile;

use Saki\Util\ArrayList;
use Saki\Util\Immutable;
use Saki\Util\PriorityComparable;
use Saki\Util\Utils;

class Tile implements Immutable {
    use PriorityComparable;

    function getPriority() {
        return TileFactory::create()->toValueID($this->tileType, $this->number, $this->isRedDora());
    }

    const REGEX_SUIT_NUMBER = '[0-9]'; // 0 means red dora 5
    const REGEX_SUIT_TILE = '(' . self::REGEX_SUIT_NUMBER . TileType::REGEX_SUIT_TYPE . ')';
    const REGEX_HONOR_TILE = TileType::REGEX_HONOR_TYPE;
    const REGEX_TILE = '(' . self::REGEX_SUIT_TILE . '|' . self::REGEX_HONOR_TILE . ')';

    static function validString(string $s) {
        $regex = '/^' . self::REGEX_TILE . '$/';
        return preg_match($regex, $s) === 1;
    }

    static function fromString(string $s) {
        if (!self::validString($s)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $s[%s] for Tile.', $s)
            );
        }

        // will be validated in create()
        if (strlen($s) == 1) {
            return Tile::create(TileType::fromString($s));
        } elseif (strlen($s) == 2) {
            if ($s[0] == '0') {
                return Tile::create(TileType::fromString($s[1]), 5, true);
            } else {
                return Tile::create(TileType::fromString($s[1]), intval($s[0]));
            }
        }

        throw new \LogicException();
    }

    protected static function valid(TileType $tileType, $number = null, bool $isRedDora = false) {
        if ($tileType->isSuit()) {
            return is_int($number) && Utils::inRange($number, 1, 9) && ($isRedDora === false || $number === 5);
        } elseif ($tileType->isHonor()) {
            return $number === null && $isRedDora == false;
        } else {
            throw new \LogicException();
        }
    }

    /**
     * @param TileType $tileType
     * @param null|int $number
     * @param bool $isRedDora
     * @return Tile
     */
    static function create(TileType $tileType, $number = null, bool $isRedDora = false) {
        if (!self::valid($tileType, $number, $isRedDora)) {
            throw new \InvalidArgumentException(
                "Invalid \$tileType[$tileType], \$number[$number].Remind that \$number should be a int."
            );
        }
        $generator = function () use ($tileType, $number, $isRedDora) {
            return new Tile($tileType, $number, $isRedDora);
        };
        return TileFactory::create()->getOrGenerateTile($tileType, $number, $isRedDora, $generator);
    }

    /**
     * @param TileType $tileType
     * @return $this
     */
    static function getSuitList(TileType $tileType) {
        return (new ArrayList(range(1, 9)))->select(function ($v) use ($tileType) {
            return Tile::create($tileType, $v);
        });
    }

    /**
     * @param int $n
     * @return TileList
     */
    static function getWindList(int $n = 4) {
        $valid = in_array(4, range(1, 4));
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        return (new TileList([Tile::fromString('E'), Tile::fromString('S'), Tile::fromString('W'), Tile::fromString('N')]))
            ->take(0, $n);
    }

    /**
     * @return TileList
     */
    static function getDragonList() {
        return new TileList([Tile::fromString('C'), Tile::fromString('P'), Tile::fromString('F')]);
    }

    /**
     * syntactic sugar.
     * @param bool $compareIsRedDora
     * @return \Closure
     */
    static function getEqual(bool $compareIsRedDora) {
        return function (Tile $a, Tile $b) use ($compareIsRedDora) {
            return $a->equalTo($b, $compareIsRedDora);
        };
    }

    private $tileType;
    private $number;

    // be private to support singleton
    private function __construct(TileType $tileType, $number, $isRedDora) {
        // already validated by create()
        $this->tileType = $tileType;
        $this->number = $number;
        // $isRedDora already handled by TileFactory
    }

    /**
     * @param Tile $other
     * @param bool $compareIsRedDora
     * @return bool
     */
    function equalTo(Tile $other, bool $compareIsRedDora) {
        return $this->tileType == $other->tileType
        && $this->number == $other->number
        && (!$compareIsRedDora || $this->isRedDora() == $other->isRedDora());
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->toFormatString(true);
    }

    /**
     * @param bool $considerRedDora
     * @return string
     */
    function toFormatString(bool $considerRedDora) {
        $numberString = $this->isSuit() ? ($considerRedDora && $this->isRedDora() ? 0 : $this->getNumber()) : '';
        $typeString = $this->getTileType()->__toString();
        return sprintf('%s%s', $numberString, $typeString);
    }

    function getTileType() {
        return $this->tileType;
    }

    function getNumber() {
        if (!$this->tileType->isSuit()) {
            throw new \LogicException('getNumber() is not supported on non-suit tile.');
        }
        return $this->number;
    }

    function isRedDora() {
        return TileFactory::create()->isRedDoraTile($this);
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
    function getNextTile(int $offset = 1) {
        $currentType = $this->getTileType();
        if ($currentType->isSuit()) {
            $a = self::getSuitList($currentType);
        } elseif ($currentType->isWind()) {
            $a = self::getWindList();
        } elseif ($currentType->isDragon()) {
            $a = self::getDragonList();
        } else {
            throw new \LogicException();
        }
        return $a->getCyclicNext($this, $offset);
    }

    /**
     * @param Tile $other
     * @return int offset = this - other. e.x. E.getWindOffsetFrom(S) = -1 .
     */
    function getWindOffsetFrom(Tile $other) {
        $windList = Tile::getWindList();
        return $windList->getIndex($this) - $windList->getIndex($other); // validate
    }

    function getWindOffsetTo(Tile $other) {
        return -$this->getWindOffsetFrom($other);
    }

    function assertWind() {
        if (!$this->isWind()) {
            throw new \LogicException();
        }
    }
}