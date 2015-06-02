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
    private $numberOrHonor;
    private $suit;

    static function valid($numberOrHonor, $suit = null) {
        return self::validNumber($numberOrHonor) && SuitConst::validValue($suit)
        || (HonorConst::validValue($numberOrHonor) && $suit === null);
    }

    static function validNumber($number) {
        return is_int($number) && 1 <= $number && $number <= 9;
    }

    function __construct($numberOrHonor, $suit = null) {
        if (!self::valid($numberOrHonor, $suit)) {
            throw new \InvalidArgumentException();
        }
        $this->numberOrHonor = $numberOrHonor;
        $this->suit = $suit;
    }

    function  isSuit() {
        return $this->suit !== null;
    }

    function getSuit() {
        if (!$this->isSuit()) {
            throw new \BadMethodCallException();
        }
        return $this->suit;
    }

    function getNumber() {
        if (!$this->isSuit()) {
            throw new \BadMethodCallException();
        }
        return $this->numberOrHonor;
    }

    function isHonor() {
        return !$this->isSuit();
    }

    function getHonor() {
        if (!$this->isHonor()) {
            throw new \BadMethodCallException();
        }
        return $this->numberOrHonor;
    }

    function getDisplayOrder() {
        if ($this->isSuit()) {
            $base = [
                SuitConst::CHARACTER => 0,
                SuitConst::DOT => 9,
                SuitConst::BAMBOO => 18,
            ][$this->getSuit()];
            return 34 - $base + $this->getNumber();
        } else {
            return 34 - [
                HonorConst::EAST => 28,
                HonorConst::SOUTH => 29,
                HonorConst::WEST => 30,
                HonorConst::NORTH => 31,
                HonorConst::RED => 32,
                HonorConst::GREEN => 33,
                HonorConst::WHITE => 34,
            ][$this->getHonor()];
        }
    }

    function getDisplayText() {
        if ($this->isSuit()) {
            return $this->getNumber() . SuitConst::toString($this->getSuit());
        } else {
            return HonorConst::toString($this->getHonor());
        }
    }

    function __toString() {
        return $this->getDisplayText();
    }
}