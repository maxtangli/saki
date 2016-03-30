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
    const REGEX_HONOR_TOKEN = Tile::REGEX_HONOR_TILE;
    const REGEX_NOT_EMPTY_LIST = '(' . self::REGEX_SUIT_TOKEN . '|' . self::REGEX_HONOR_TOKEN . ')+';
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
                if ($cType->isHonor()) {
                    $honorTile = Tile::getInstance($cType);
                    array_unshift($tiles, $honorTile);
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
     * @return ArrayList
     */
    function toTileTypeList() {
        return (new ArrayList())->fromSelected($this, function (Tile $tile) {
            return $tile->getTileType();
        });
    }

    /**
     * @return ArrayList
     */
    function toTileNumberList() {
        return (new ArrayList())->fromSelected($this, function (Tile $tile) {
            return $tile->getNumber(); // validate
        });
    }

    /**
     * @param int $firstPartLength
     * @return TileList[] list($beginTileList, $remainTileList)
     */
    function toTwoPart(int $firstPartLength) {
        $l1 = $this->getCopy()->take(0, $firstPartLength);
        $l2 = $this->getCopy()->take($firstPartLength);
        return [$l1, $l2];
    }

    /**
     * @return HandSize
     */
    function getHandSize() {
        return new HandSize($this->count());
    }

    protected function assertCompletePrivateHand() {
        if (!$this->getHandSize()->isCompletePrivate()) {
            throw new \LogicException(
                sprintf('Assertion Failed. Require complete-private-hand but [%s] given.', $this->__toString())
            );
        }
    }

    function isAllSuit() {
        return $this->isAll(function (Tile $tile) {
            return $tile->isSuit();
        });
    }

    function isAllSameSuit() {
        return $this->isAllSuit() && $this->toTileTypeList()->isSame();
    }

    function isAllSimple() {
        return $this->isAll(function (Tile $tile) {
            return $tile->isSimple();
        });
    }

    function isAllTerminal() {
        return $this->isAll(function (Tile $tile) {
            return $tile->isTerminal();
        });
    }

    function isAllTerminalOrHonor() {
        return $this->isAll(function (Tile $tile) {
            return $tile->isTerminalOrHonor();
        });
    }

    function isAllHonor() {
        return $this->isAll(function (Tile $tile) {
            return $tile->isHonor();
        });
    }

    function isAnyTerminalOrHonor() {
        return $this->isAny(function (Tile $tile) {
            return $tile->isTerminalOrHonor();
        });
    }

    function isAnyTerminal() {
        return $this->isAny(function (Tile $tile) {
            return $tile->isTerminal();
        });
    }

    function isNineKindsOfTerminalOrHonor() {
        $this->assertCompletePrivateHand();

        $uniqueTerminalOrHonorCount = $this->getCopy()->where(function (Tile $tile) {
            return $tile->isTerminalOrHonor();
        })->distinct()->count();
        return $uniqueTerminalOrHonorCount >= 9;
    }

    function isThirteenOrphan(bool $isPairWaiting, Tile $targetTile = null) {
        $this->assertCompletePrivateHand();

        $valid = !$isPairWaiting || $targetTile;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        if (!$this->isAllTerminalOrHonor()) {
            return false;
        }

        $requiredPartTileList = TileList::fromString('19m19p19sESWNCFP');
        // note: for a all terminalOrHonor hand, the remain one tile must be terminalOrHonor.
        if (!$isPairWaiting) {
            return $this->valueExist($requiredPartTileList->toArray());
        } else {
            return $this->getCopy()->remove($targetTile)->valueExist($requiredPartTileList);
        }
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
        $hasHonor = $suitList->count() != $this->count();
        return $isSuitSameColor && ($isFull ? !$hasHonor : $hasHonor);
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
        return $this->isAll($isAllGreenTile);
    }

    /**
     * @return $this
     */
    function orderByTileID() {
        return $this->orderByAscending(Tile::getComparator());
    }
}

