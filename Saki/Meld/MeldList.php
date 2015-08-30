<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileSortedList;
use Saki\Util\ArrayLikeObject;

class MeldList extends ArrayLikeObject {
    static function validString($s) {
        $meldStrings = !empty($s) ? explode(',', $s) : [];
        foreach ($meldStrings as $meldString) {
            if (!Meld::validString($meldString)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $s
     * @return MeldList
     */
    static function fromString($s) {
        if (!static::validString($s)) {
            throw new \InvalidArgumentException("Invalid MeldList string[$s]");
        }
        $meldStrings = !empty($s) ? explode(',', $s) : [];
        $melds = array_map(function ($s) {
            return Meld::fromString($s);
        }, $meldStrings);
        return new static($melds);
    }

    function __construct(array $melds) {
        parent::__construct($melds);
    }

    /**
     * @return Meld[]
     */
    function toArray() {
        return parent::toArray();
    }

    /**
     * @param int $offset
     * @return Meld
     */
    function offsetGet($offset) {
        return parent::offsetGet($offset);
    }

    function __toString() {
        $meldStrings = array_map(function (Meld $meld) {
            return $meld->__toString();
        }, $this->toArray());
        return implode(',', $meldStrings);
    }

    function getFilteredMeldList(callable $filter) {
        $melds = array_filter($this->toArray(), $filter);
        return new self(array_values($melds));
    }

    function getFilteredTypesMeldList(array $targetMeldTypes) {
        return $this->getFilteredMeldList(function (Meld $meld) use ($targetMeldTypes) {
            return in_array($meld->getMeldType(), $targetMeldTypes);
        });
    }

    function toSortedTileList() {
        $l = new TileSortedList([]);
        foreach ($this as $meld) {
            $l->insert($meld->toArray(), 0);
        }
        return $l;
    }

    function full() {
        return $this->toSortedTileList()->validPrivatePhaseCount();
    }

    function tileExist(Tile $tile) {
        return $this->any(function (Meld $meld) use ($tile) {
            return $meld->valueExist($tile);
        });
    }
}