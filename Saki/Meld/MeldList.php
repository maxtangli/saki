<?php
namespace Saki\Meld;

use Saki\Tile;
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
    public function toArray() {
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

    /**
     * @param Meld $meld
     * @param int|null default is last pos
     */
    function insert(Meld $meld, $pos = null) {
        parent::insert($meld, $pos);
    }

    function canPlusKong(Tile $tile) {
        foreach ($this as $k => $meld) {
            if ($meld->canPlusKong($tile)) {
                return true;
            }
        }
        return false;
    }

    function plusKong(Tile $tile, $forceExposed) {
        foreach ($this as $k => $meld) {
            if ($meld->canPlusKong($tile)) {
                $newMelds = $this->toArray();
                $newMelds[$k] = $meld->getPlusKongMeld($tile, $forceExposed);
                $this->setInnerArray($newMelds);
                return;
            }
        }
        throw new \InvalidArgumentException("Invalid plusKong \$tile[$tile] for MeldList \$this[$this]");
    }
}