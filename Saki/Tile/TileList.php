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
                array_unshift($tiles, new Tile($tileType, intval($c)));
            } else {
                $tileType = TileType::fromString($c);
                if ($tileType->isHonor()) {
                    array_unshift($tiles, new Tile($tileType));
                }
            }
        }

        return new static($tiles);
    }

    function __construct(array $tiles) {
        parent::__construct($tiles);
    }

    function __toString() {
        // 123m456p789s東東東中中
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

    /**
     * @param int $firstPartLength
     * @return TileList[] list($beginTileList, $remainTileList)
     */
    function getCutInTwoTileLists($firstPartLength) {
        $tiles = $this->toArray();
        $tiles1 = array_slice($tiles, 0, $firstPartLength);
        $tiles2 = array_slice($tiles, $firstPartLength);
        return [new self($tiles1), new self($tiles2)];
    }

    /**
     * @return Tile[]
     */
    function toArray() {
        return parent::toArray();
    }

    /**
     * @param int $offset
     * @return Tile
     */
    function offsetGet($offset) {
        return parent::offsetGet($offset);
    }

    function validPrivatePhaseCount() {
        return $this->count() % 3 == 2;
    }

    function validPublicPhaseCount() {
        return $this->count() % 3 == 1;
    }
}

