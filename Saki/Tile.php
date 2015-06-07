<?php

namespace Saki;

/*
 Suit Dot / Bamboo / Character
 Rank 1-9
Honor
 Wind East / South / West / North Wind
 Dragon Red / Green / White Dragon

Tile
Eyes
Meld Sequence / (Exposed / Concealed) Triplet / (Exposed / Concealed) Kong

## task

- [x] new a tile
- []

## note

- Agile saves tons of time.

 */

class Tile {
    const REGEX_SUIT_NUMBER = '[1-9]';
    const REGEX_SUIT_TILE = '('.self::REGEX_SUIT_NUMBER.TileType::REGEX_SUIT_TYPE.')';
    const REGEX_HONOR_TILE = TileType::REGEX_HONOR_TYPE;
    const REGEX_TILE = '('.self::REGEX_SUIT_TILE.'|'.self::REGEX_HONOR_TILE.')';

    static function valid(TileType $tileType, $number) {
        return ($tileType->isSuit() && self::validNumber($number))
        || ($tileType->isHonor() && $number === null);
    }

    static function validNumber($number) {
        return is_int($number) && 1 <= $number && $number <= 9;
    }

    static function validString($s) {
        $regex = '/^'.self::REGEX_TILE.'$/';
        return preg_match($regex, $s)===1;
    }

    static function fromString($s) {
        if (!self::validString($s)) {
            throw new \InvalidArgumentException();
        }
        if (strlen($s)==1) {
            return new self(TileType::fromString($s));
        } else {
            return new self(TileType::fromString($s[1]), intval($s[0]));
        }
    }

    private $tileType;
    private $number;

    function __construct(TileType $tileType, $number = null) {
        if (!self::valid($tileType, $number)) {
            throw new \InvalidArgumentException("Invalid argument \$tileType[$tileType], \$number[$number].Remind that \$number should be a int.");
        }
        $this->tileType = $tileType;
        $this->number = $number;
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

    function __toString() {
        $tileType = $this->getTileType();
        return $tileType->isSuit() ? $this->getNumber() . $tileType->__toString() : $tileType->__toString();
    }
}