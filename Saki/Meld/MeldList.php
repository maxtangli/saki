<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
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
            throw new \InvalidArgumentException();
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
        $meldStrings = array_map(function ($meld) {
            return $meld->__toString();
        }, $this->toArray());
        return implode(',', $meldStrings);
    }

    function getFilteredMeldList(callable $filter) {
        $melds = array_filter($this->toArray(), $filter);
        return new self(array_values($melds));
    }

    function canPlusKong(Tile $tile) {
        foreach ($this as $k => $meld) {
            if ($meld->canPlusKong($tile)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Tile $tile
     * @param $forceExposed
     * @return Meld
     */
    function plusKong(Tile $tile, $forceExposed) {
        foreach ($this as $k => $meld) {
            if ($meld->canPlusKong($tile)) {
                $newMelds = $this->toArray();
                $newMeld = $meld->getPlusKongMeld($tile, $forceExposed);
                $newMelds[$k] = $newMeld;
                $this->setInnerArray($newMelds);
                return $newMeld;
            }
        }
        throw new \InvalidArgumentException("Invalid plusKong \$tile[$tile] for MeldList \$this[$this]");
    }
}