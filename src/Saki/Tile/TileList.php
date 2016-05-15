<?php
namespace Saki\Tile;

use Saki\Util\ArrayList;

/**
 * A sequence of Tile.
 * @package Saki\Tile
 */
class TileList extends ArrayList {
    const REGEX_EMPTY_LIST = '()';
    const REGEX_SUIT_TOKEN = '(' . Tile::REGEX_SUIT_NUMBER . '+' . TileType::REGEX_SUIT_TYPE . ')';
    const REGEX_HONOUR_TOKEN = Tile::REGEX_HONOUR_TILE;
    const REGEX_NOT_EMPTY_LIST = '(' . self::REGEX_SUIT_TOKEN . '|' . self::REGEX_HONOUR_TOKEN . ')+';
    const REGEX_LIST = '(' . self::REGEX_EMPTY_LIST . '|' . self::REGEX_NOT_EMPTY_LIST . ')';

    /**
     * @param string $s
     * @return bool
     */
    static function validString(string $s) {
        $regex = '/^' . self::REGEX_LIST . '$/';
        return preg_match($regex, $s) === 1;
    }

    /**
     * @param string $s
     * @return TileList
     */
    static function fromString(string $s) {
        if (!static::validString($s)) {
            throw new \InvalidArgumentException("Invalid \$s[$s].");
        }

        $tiles = [];
        /** @var TileType $lastNumberTileType */
        $lastNumberTileType = null;
        for ($i = strlen($s) - 1; $i >= 0; --$i) {
            $c = $s[$i];
            if (is_numeric($c)) {
                $numberTile = Tile::fromString($c . $lastNumberTileType->__toString());
                array_unshift($tiles, $numberTile);
            } else {
                $cType = TileType::fromString($c);
                if ($cType->isHonour()) {
                    $honourTile = Tile::create($cType);
                    array_unshift($tiles, $honourTile);
                } else {
                    $lastNumberTileType = $cType;
                }
            }
        }

        return new self($tiles);
    }

    static function fromNumbers(array $numbers, TileType $suitType) {
        if (!$suitType->isSuit()) {
            throw new \InvalidArgumentException();
        }
        $s = implode($numbers) . $suitType;
        return self::fromString($s);
    }

    function __toString() {
        // e.x. 123m456p789sEEECC
        $s = "";
        $tiles = $this->toArray();
        $len = count($tiles);
        for ($i = 0; $i < $len; ++$i) {
            $tile = $tiles[$i];
            if ($tile->getTileType()->isSuit()) {
                $tileString = $tile->__toString();
                $doNotPrintSuit = isset($tiles[$i + 1])
                    && $tiles[$i + 1]->getTileType()->isSuit()
                    && $tiles[$i + 1]->getTileType() == $tile->getTileType();
                $s .= $doNotPrintSuit ? $tileString[0] : $tileString;
            } else {
                $s .= $tile;
            }
        }
        return $s;
    }

    /**
     * @param bool $sort
     * @return string
     */
    function toFormatString(bool $sort) {
        return $sort ? $this->getCopy()->orderByTileID()->__toString() : $this->__toString();
    }

    /**
     * @return ArrayList
     */
    function toTileTypeList() {
        return (new ArrayList())->fromSelect($this, function (Tile $tile) {
            return $tile->getTileType();
        });
    }

    /**
     * @return ArrayList
     */
    function toTileNumberList() {
        return (new ArrayList())->fromSelect($this, function (Tile $tile) {
            return $tile->getNumber(); // validate
        });
    }

    /**
     * @param int $firstPartLength
     * @return TileList[] Returns [$firstPartTileList, $remainTileList].
     */
    function toTwoPart(int $firstPartLength) {
        $l1 = $this->getCopy()->take(0, $firstPartLength);
        $l2 = $this->getCopy()->take($firstPartLength);
        return [$l1, $l2];
    }

    /**
     * @param array $firstPartTiles
     * @return TileList[]|bool Returns [$firstPartTileList, $remainTileList] if success, false otherwise.
     */
    function toTwoCut(array $firstPartTiles) {
        if (!$this->valueExist($firstPartTiles)) {
            return false;
        }
        $l1 = new TileList($firstPartTiles);
        $l2 = $this->getCopy()->remove($firstPartTiles);
        return [$l1, $l2];
    }

    /**
     * @return TileListSize
     */
    function getHandSize() {
        return new TileListSize($this->count());
    }

    protected function assertCompletePrivateHand() {
        if (!$this->getHandSize()->isComplete()) {
            throw new \LogicException(
                sprintf('Assertion Failed. Require complete-private-hand but [%s] given.', $this->__toString())
            );
        }
    }

    function isAllSuit() {
        return $this->all(function (Tile $tile) {
            return $tile->isSuit();
        });
    }

    function isAllSameSuit() {
        return $this->isAllSuit() && $this->toTileTypeList()->isSame();
    }

    function isAllSimple() {
        return $this->all(function (Tile $tile) {
            return $tile->isSimple();
        });
    }

    function isAllTerm() {
        return $this->all(function (Tile $tile) {
            return $tile->isTerm();
        });
    }

    function isAllTermOrHonour() {
        return $this->all(function (Tile $tile) {
            return $tile->isTermOrHonour();
        });
    }

    function isAllHonour() {
        return $this->all(function (Tile $tile) {
            return $tile->isHonour();
        });
    }

    function isAnyTermOrHonour() {
        return $this->any(function (Tile $tile) {
            return $tile->isTermOrHonour();
        });
    }

    function isAnyTerm() {
        return $this->any(function (Tile $tile) {
            return $tile->isTerm();
        });
    }

    function isNineKindsOfTermOrHonour() {
        $this->assertCompletePrivateHand();

        $uniqueTermOrHonourCount = $this->getCopy()->where(function (Tile $tile) {
            return $tile->isTermOrHonour();
        })->distinct()->count();
        return $uniqueTermOrHonourCount >= 9;
    }

    function isFlush(bool $isFull) {
        $this->assertCompletePrivateHand();

        $suitList = $this->getCopy()->where(function (Tile $tile) {
            return $tile->isSuit();
        });
        if ($suitList->isEmpty()) {
            return false;
        }

        $isSuitSameColor = $suitList->getCopy()->select(function (Tile $tile) {
                return $tile->getTileType();
            })->distinct()->count() == 1;
        $hasHonour = $suitList->count() != $this->count();
        return $isSuitSameColor && ($isFull ? !$hasHonour : $hasHonour);
    }

    function isNineGates(bool $isPure, Tile $targetTile = null) {
        $this->assertCompletePrivateHand();

        $valid = !$isPure || $targetTile;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        if (!$this->isFlush(true)) {
            return false;
        }
        /** @var Tile $firstTile */
        $firstTile = $this[0];
        $tileTypeString = $firstTile->getTileType()->__toString();
        $requiredPartTileList = TileList::fromString('1112345678999' . $tileTypeString);
        // note : for a full flush hand, the remain one tile must be same color.
        if (!$isPure) {
            return $this->valueExist($requiredPartTileList->toArray());
        } else {
            return $this->getCopy()->remove($targetTile)->valueExist($requiredPartTileList);
        }
    }

    function isAllGreen() {
        $this->assertCompletePrivateHand();

        $greenTileList = TileList::fromString('23468sF');
        $isAllGreenTile = function (Tile $tile) use ($greenTileList) {
            return $greenTileList->valueExist($tile);
        };
        return $this->all($isAllGreenTile);
    }

    /**
     * @return $this
     */
    function orderByTileID() {
        return $this->orderByAscending(Tile::getComparator());
    }
}

