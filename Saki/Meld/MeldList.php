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

    function toSortedTileList() {
        $l = new TileSortedList([]);
        foreach ($this as $meld) {
            $l->insert($meld->toArray(), 0);
        }
        return $l;
    }

    function toFilteredMeldList(callable $filter) {
        $melds = array_filter($this->toArray(), $filter);
        return new self(array_values($melds));
    }

    function toFilteredTypesMeldList(array $targetMeldTypes) {
        return $this->toFilteredMeldList(function (Meld $meld) use ($targetMeldTypes) {
            return in_array($meld->getMeldType(), $targetMeldTypes);
        });
    }

    function full() {
        return $this->toSortedTileList()->validPrivatePhaseCount();
    }

    function tileExist(Tile $tile) {
        return $this->any(function (Meld $meld) use ($tile) {
            return $meld->valueExist($tile);
        });
    }

    function isSevenUniquePairs() {
        $pairs = $this->toFilteredTypesMeldList([PairMeldType::getInstance()])->toArray();
        $isUnique = array_unique($pairs) == $pairs;
        return count($pairs) == 7 && $isUnique;
    }

    function isFourWinSetAndOnePair() {
        $winSetList = $this->toFilteredMeldList(function(Meld $meld) {
            return $meld->getWinSetType()->isWinSet();
        });
        $pairList = $this->toFilteredTypesMeldList([PairMeldType::getInstance()]);
        return $winSetList->count() == 4 && $pairList->count() == 1;
    }

    function isFourRunAndOnePair() {
        $runList = $this->toFilteredTypesMeldList([RunMeldType::getInstance()]);
        $pairList = $this->toFilteredTypesMeldList([PairMeldType::getInstance()]);
        return $runList->count() == 4 && $pairList->count() == 1;
    }

    function isFourTripleOrQuadAndOnePair($requireConcealedTripleOrQuad = false) {
        $tripleOrQuadList = $this->toFilteredTypesMeldList([TripleMeldType::getInstance(), QuadMeldType::getInstance()]);
        $pairList = $this->toFilteredTypesMeldList([PairMeldType::getInstance()]);
        $matchConcealed = !$requireConcealedTripleOrQuad || $tripleOrQuadList->all(function (Meld $meld) {
                return $meld->isConcealed();
            });
        return $tripleOrQuadList->count() == 4 && $pairList->count() == 1 && $matchConcealed;
    }
}