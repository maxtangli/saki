<?php
namespace Saki\Game\Tile;

use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Game\Tile
 */
class TileList extends ArrayList {
    //region factory
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
                    $honourTile = Tile::fromString($c);
                    array_unshift($tiles, $honourTile);
                } else {
                    $lastNumberTileType = $cType;
                }
            }
        }

        return new self($tiles);
    }

    /**
     * @param array $numbers
     * @param TileType $suitType
     * @return TileList
     */
    static function fromNumbers(array $numbers, TileType $suitType) {
        if (!$suitType->isSuit()) {
            throw new \InvalidArgumentException();
        }
        $s = implode($numbers) . $suitType;
        return self::fromString($s);
    }
    //endregion

    //region convert
    /**
     * @return string
     */
    function __toString() {
        $s = ''; // e.g. 123mEEE456pCC

        // reverse iterate raw string and filter duplicated 'mps'
        $rawString = implode('', $this->toArray()); // e.g. 1m2m3mEEE4p5p6pCC
        $lastSuitType = false;
        $len = strlen($rawString);
        for ($i = $len - 1; $i >= 0; --$i) {
            $c = $rawString[$i];
            if (strpos('mps', $c) !== false) {
                if ($c == $lastSuitType) {
                    continue;
                } else {
                    $lastSuitType = $c;
                }
            } elseif (!is_numeric($c)) {
                $lastSuitType = false;
            }
            $s = $c . $s;
        }

        return $s;
    }

    /**
     * @param bool $hide
     * @return array e.g. ['1s', '2s']
     */
    function toJson(bool $hide = false) {
        return $hide
            ? $this->toRepeatArray('O')
            : $this->toArray(Utils::getToStringCallback());
    }

    /**
     * @param bool $sort
     * @return string
     */
    function toSortedString(bool $sort) {
        return $sort ? $this->getCopy()->orderByTileID()->__toString() : $this->__toString();
    }

    /**
     * @return TileList
     */
    function toTileList() {
        return new TileList($this->toArray());
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
        try {
            $l2 = $this->getCopy()->remove($firstPartTiles);
        } catch (\Exception $e) {
            return false;
        }

        $l1 = new TileList($firstPartTiles);
        return [$l1, $l2];
    }
    //endregion

    //region property
    /**
     * @return TileListSize
     */
    function getSize() {
        return new TileListSize($this->count());
    }

    protected function assertCompletePrivateHand() {
        if (!$this->getSize()->isComplete()) {
            throw new \LogicException(
                sprintf('Assertion Failed. Require complete-private-hand but [%s] given.', $this->__toString())
            );
        }
    }
    //endregion

    //region predicate
    /**
     * @return bool
     */
    function isAllSuit() {
        return $this->all(function (Tile $tile) {
            return $tile->isSuit();
        });
    }

    /**
     * @return bool
     */
    function isAllSameSuit() {
        return $this->isAllSuit() && $this->toTileTypeList()->isSame();
    }

    /**
     * @return bool
     */
    function isAllSimple() {
        return $this->all(function (Tile $tile) {
            return $tile->isSimple();
        });
    }

    /**
     * @return bool
     */
    function isAllTerm() {
        return $this->all(function (Tile $tile) {
            return $tile->isTerm();
        });
    }

    /**
     * @return bool
     */
    function isAllTermOrHonour() {
        return $this->all(function (Tile $tile) {
            return $tile->isTermOrHonour();
        });
    }

    /**
     * @return bool
     */
    function isAllHonour() {
        return $this->all(function (Tile $tile) {
            return $tile->isHonour();
        });
    }

    /**
     * @return bool
     */
    function isAnyTermOrHonour() {
        return $this->any(function (Tile $tile) {
            return $tile->isTermOrHonour();
        });
    }

    /**
     * @return bool
     */
    function isAnyTerm() {
        return $this->any(function (Tile $tile) {
            return $tile->isTerm();
        });
    }

    /**
     * @return bool
     */
    function isNineKindsOfTermOrHonour() {
        $this->assertCompletePrivateHand();

        $uniqueTermOrHonourCount = $this->getCopy()->where(function (Tile $tile) {
            return $tile->isTermOrHonour();
        })->distinct()->count();
        return $uniqueTermOrHonourCount >= 9;
    }

    /**
     * @param bool $isFull
     * @return bool
     */
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

    /**
     * @param bool $isPure
     * @param Tile|null $targetTile
     * @return bool
     */
    function isNineGates(bool $isPure, Tile $targetTile = null) {
        $this->assertCompletePrivateHand();

        $valid = !$isPure || $targetTile;
        if (!$valid) {
            throw new \InvalidArgumentException('Require targetTile for pure case.');
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
            return $this->getCopy()->remove($targetTile)->valueExist($requiredPartTileList->toArray());
        }
    }

    /**
     * @return bool
     */
    function isAllGreen() {
        $this->assertCompletePrivateHand();

        $greenTileList = TileList::fromString('23468sF');
        $isAllGreenTile = function (Tile $tile) use ($greenTileList) {
            return $greenTileList->valueExist($tile);
        };
        return $this->all($isAllGreenTile);
    }
    //endregion

    //region operation
    /**
     * @return $this
     */
    function orderByTileID() {
        $selector = function (Tile $tile) {
            return $tile->getPriority();
        };
        return $this->orderByAscending($selector);
    }
    //endregion
}

