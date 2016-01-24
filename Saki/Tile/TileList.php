<?php
namespace Saki\Tile;

use Saki\Util\ArrayLikeObject;

class TileList extends ArrayLikeObject {
    const REGEX_EMPTY_LIST = '()';
    const REGEX_SUIT_TOKEN = '(' . Tile::REGEX_SUIT_NUMBER . '+' . TileType::REGEX_SUIT_TYPE . ')';
    const REGEX_HONOR_TOKEN = Tile::REGEX_HONOR_TILE;
    const REGEX_NOT_EMPTY_LIST = '(' . self::REGEX_SUIT_TOKEN . '|' . self::REGEX_HONOR_TOKEN . ')+';
    const REGEX_LIST = '(' . self::REGEX_EMPTY_LIST . '|' . self::REGEX_NOT_EMPTY_LIST . ')';

    static function validString($s) {
        $regex = '/^' . self::REGEX_LIST . '$/';
        return preg_match($regex, $s) === 1;
    }

    /**
     * @param string $s
     * @return TileList
     */
    static function fromString($s) {
        if (!static::validString($s)) {
            throw new \InvalidArgumentException("Invalid \$s[$s].");
        }

        $tiles = [];
        $tileType = null;
        for ($i = strlen($s) - 1; $i >= 0; --$i) {
            $c = $s[$i];
            if (is_numeric($c)) {
                array_unshift($tiles, Tile::getInstance($tileType, intval($c)));
            } else {
                $tileType = TileType::fromString($c);
                if ($tileType->isHonor()) {
                    array_unshift($tiles, Tile::getInstance($tileType));
                }
            }
        }

        return new static($tiles);
    }

    function __construct(array $tiles, $writable = true) {
        parent::__construct($tiles, $writable);
    }

    function sort() {
        // &getInnerArrayReferenceUnsafe() won't make it faster, but results in shorter code.
        usort($this->getInnerArrayReferenceUnsafe(), function (Tile $a, Tile $b) {
            return $a->getDisplayValueID() - $b->getDisplayValueID();
        });
        return $this;
    }

    function __toString() {
        // 123m456p789sEEECC
        $s = "";
        $tiles = $this->toArray();
        $len = count($tiles);
        for ($i = 0; $i < $len; ++$i) {
            $tile = $tiles[$i];
            if ($tile->getTileType()->isSuit()) {
                $doNotPrintSuit = isset($tiles[$i + 1]) && $tiles[$i + 1]->getTileType()->isSuit() && $tiles[$i + 1]->getTileType() == $tile->getTileType();
                $s .= $doNotPrintSuit ? $tile->getNumber() : $tile;
            } else {
                $s .= $tile;
            }
        }
        return $s;
    }

    function toTileList() {
        return new TileList($this->toArray());
    }

    function getHandCount() {
        return new HandCount($this->count());
    }

    function isCompletePrivateHand() {
        return $this->count() == 14;
    }

    protected function assertCompletePrivateHand() {
        if (!$this->isCompletePrivateHand()) {
            throw new \LogicException();
        }
    }

    function isPrivateHand() {
        return $this->count() % 3 == 2;
    }

    function isPublicHand() {
        return $this->count() % 3 == 1;
    }

    function isAllSuit() {
        return $this->all(function (Tile $tile) {
            return $tile->isSuit();
        });
    }

    function isAllSimple() {
        return $this->all(function (Tile $tile) {
            return $tile->isSimple();
        });
    }

    function isAllTerminal() {
        return $this->all(function (Tile $tile) {
            return $tile->isTerminal();
        });
    }

    function isAllTerminalOrHonor() {
        return $this->all(function (Tile $tile) {
            return $tile->isTerminalOrHonor();
        });
    }

    function isAllHonor() {
        return $this->all(function (Tile $tile) {
            return $tile->isHonor();
        });
    }

    function isNineKindsOfTerminalOrHonor() {
        $this->assertCompletePrivateHand();

        $uniqueTerminalOrHonorList = $this->toFilteredTileList(function (Tile $tile) {
            return $tile->isTerminalOrHonor();
        });
        $uniqueTerminalOrHonorList->unique();
        return $uniqueTerminalOrHonorList->count() >= 9;
    }

    function isThirteenOrphan($isPairWaiting, Tile $targetTileForIsPairWaitingCase = null) {
        $valid = $isPairWaiting || $targetTileForIsPairWaitingCase;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->assertCompletePrivateHand();

        $requiredPartTileList = TileList::fromString('19m19p19sESWNCFP');
        // this works because for a full terminalOrHonor hand, the remain one tile will be terminalOrHonor.
        if (!$isPairWaiting) {
            return $this->valueExist($requiredPartTileList->toArray());
        } else {
            $publicPhaseTileList = new TileList($this->toArray());
            $publicPhaseTileList->removeByValue($targetTileForIsPairWaitingCase);
            return $publicPhaseTileList->valueExist($requiredPartTileList);
        }
    }

    function isFlush($isFull) {
        $this->assertCompletePrivateHand();

        $suitList = $this->toFilteredTileList(function (Tile $tile) {
            return $tile->isSuit();
        });
        if ($suitList->count() == 0) {
            return false;
        }

        $uniqueSuitColorList = new ArrayLikeObject($suitList->toArray(function (Tile $tile) {
            return $tile->getTileType();
        }));
        $uniqueSuitColorList->unique();
        $isSuitSameColor = $uniqueSuitColorList->count() == 1;

        $hasHonor = $suitList->count() != $this->count();

        return $isFull ? $isSuitSameColor && !$hasHonor : $isSuitSameColor && $hasHonor;
    }

    function isNineGates($isPure, Tile $targetTileForIsPureCase = null) {
        $valid = !$isPure || $targetTileForIsPureCase;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $this->assertCompletePrivateHand();

        if (!$this->isFlush(true)) {
            return false;
        }

        $tileTypeString = $this[0]->getTileType()->__toString();
        $requiredPartTileList = TileList::fromString('1112345678999' . $tileTypeString);
        // this works because for a full flush hand, the remain one tile will be same color.
        if (!$isPure) {
            return $this->valueExist($requiredPartTileList->toArray());
        } else {
            $publicPhaseTileList = new TileList($this->toArray());
            $publicPhaseTileList->removeByValue($targetTileForIsPureCase);
            return $publicPhaseTileList->valueExist($requiredPartTileList);
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
     * @param int $firstPartLength
     * @return TileList[] list($beginTileList, $remainTileList)
     */
    function toTwoPart($firstPartLength) {
        $tiles = $this->toArray();
        $tiles1 = array_slice($tiles, 0, $firstPartLength);
        $tiles2 = array_slice($tiles, $firstPartLength);
        return [new TileList($tiles1), new TileList($tiles2)];
    }

    function toFilteredTileList(callable $filter) {
        return new TileList($this->toFilteredArray($filter));
    }

    /**
     * @param callable|null $selector
     * @return Tile[]
     */
    function toArray(callable $selector = null) {
        return parent::toArray($selector);
    }

    /**
     * @param int $offset
     * @return Tile
     */
    function offsetGet($offset) {
        return parent::offsetGet($offset);
    }
}

