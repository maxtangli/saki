<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Tile\TileType;
use Saki\Util\ArrayLikeObject;
use Saki\Util\Singleton;

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
        return new self($this->toFilteredArray($filter));
    }

    function toFilteredTypesMeldList(array $targetMeldTypes, $exposedFlag = null) {
        return $this->toFilteredMeldList(function (Meld $meld) use ($targetMeldTypes, $exposedFlag) {
            return in_array($meld->getMeldType(), $targetMeldTypes)
            && ($exposedFlag === null || $meld->isExposed() == $exposedFlag);
        });
    }

    function isCompleteCount() {
        return $this->toSortedTileList()->isCompleteCount();
    }

    function assertCompleteCount() {
        if (!$this->isCompleteCount()) {
            throw new \LogicException();
        }
    }

    function tileExist(Tile $tile) {
        return $this->any(function (Meld $meld) use ($tile) {
            return $meld->valueExist($tile);
        });
    }

    // tileSeries

    function isSevenUniquePairs() {
        $this->assertCompleteCount();

        $pairs = $this->toFilteredTypesMeldList([PairMeldType::getInstance()])->toArray();
        $isUnique = array_unique($pairs) == $pairs;
        return count($pairs) == 7 && $isUnique;
    }

    function isFourWinSetAndOnePair() {
        $this->assertCompleteCount();

        $winSetList = $this->toFilteredMeldList(function (Meld $meld) {
            return $meld->getWinSetType()->isWinSet();
        });
        $pairList = $this->toFilteredTypesMeldList([PairMeldType::getInstance()]);
        return $winSetList->count() == 4 && $pairList->count() == 1;
    }

    function isFourRunAndOnePair() {
        $this->assertCompleteCount();

        $runList = $this->toFilteredTypesMeldList([RunMeldType::getInstance()]);
        $pairList = $this->toFilteredTypesMeldList([PairMeldType::getInstance()]);
        return $runList->count() == 4 && $pairList->count() == 1;
    }

    function isFourTripleOrQuadAndOnePair($requireConcealedTripleOrQuad = false) {
        $this->assertCompleteCount();

        $tripleOrQuadList = $this->toFilteredTypesMeldList([TripleMeldType::getInstance(), QuadMeldType::getInstance()]);
        $pairList = $this->toFilteredTypesMeldList([PairMeldType::getInstance()]);
        $matchConcealed = !$requireConcealedTripleOrQuad || $tripleOrQuadList->all(function (Meld $meld) {
                return $meld->isConcealed();
            });
        return $tripleOrQuadList->count() == 4 && $pairList->count() == 1 && $matchConcealed;
    }

    // yaku. WARNING: be careful about Meld.$exposed, especially for ArrayLikeObject search operations.

    protected function getMeldEqualsCallback() {
        return function (Meld $a, Meld $b) {
            $compareExposed = false;
            return $a->equals($b, $compareExposed);
        };
    }

    // yaku: run concerned

    function isDoubleRun($isTwoDoubleRun) {
        $this->assertCompleteCount();

        $requiredDoubleRunCount = $isTwoDoubleRun ? 2 : 1;

        $runMeldList = $this->toFilteredTypesMeldList([RunMeldType::getInstance()]);

        $uniqueRunMeldList = new MeldList($runMeldList->toArray());
        $uniqueRunMeldList->unique($this->getMeldEqualsCallback());

        return $uniqueRunMeldList->getMatchedValueCount(function (Meld $runMeld) use ($runMeldList) {
            $runMeldList->getValueCount($runMeld, $this->getMeldEqualsCallback()) >= 2;
        }) >= $requiredDoubleRunCount;
    }

    function isThreeColorRuns() {
        $this->assertCompleteCount();

        return $this->any(function (Meld $meld) {
            $this->valueExist($meld->toAllSuitTypeWinSets(), $this->getMeldEqualsCallback());
        });
    }

    function isThreeColorTriples() {
        $this->assertCompleteCount();

        return $this->any(function (Meld $meld) {
            $this->valueExist($meld->toAllSuitTypeWinSets(), $this->getMeldEqualsCallback());
        });
    }

    function isFullStraight() {
        $this->assertCompleteCount();

        $targetMeldsArray = new ArrayLikeObject([
            [Meld::fromString('123m'), Meld::fromString('456m'), Meld::fromString('789m')],
            [Meld::fromString('123p'), Meld::fromString('456p'), Meld::fromString('789p')],
            [Meld::fromString('123s'), Meld::fromString('456s'), Meld::fromString('789s')],
        ]);
        return $targetMeldsArray->any(function (array $targetMelds) {
            $this->valueExist($targetMelds, $this->getMeldEqualsCallback());
        });
    }

    // yaku: triple/quad concerned

    function isThreeConcealedTriples() {
        $this->assertCompleteCount();

        $concealedTripleMeldList = $this->toFilteredTypesMeldList(
            [TripleMeldType::getInstance(), QuadMeldType::getInstance()], false);
        return $concealedTripleMeldList->count() >= 3;
    }

    function isThreeOrFourQuads($isFour) {
        $this->assertCompleteCount();

        $quadList = $this->toFilteredTypesMeldList([QuadMeldType::getInstance()]);
        $n = $isFour ? 4 : 3;
        return $quadList->count() == $n;
    }

    // yaku: tile concerned

    function isOutsideHand($isPure) {
        $this->assertCompleteCount();

        return $this->all(function (Meld $meld) use ($isPure) {
            return $meld->isOutsideHandRun($isPure);
        });
    }

    function isAllTerminals() {
        $this->assertCompleteCount();

        return $this->all(function (Meld $meld) {
            return $meld->isTerminalWinSet();
        });
    }

    function isAllHonors() {
        $this->assertCompleteCount();

        return $this->all(function (Meld $meld) {
            return $meld->isHonorWinSet();
        });
    }

    function isAllTerminalsAndHonors() {
        $this->assertCompleteCount();

        return $this->all(function (Meld $meld) {
            return $meld->isTerminalOrHonorWinSet();
        });
    }

    function isThreeDragon($isBig) {
        $this->assertCompleteCount();

        $dragonMeldList = $this->toFilteredMeldList(function (Meld $meld) {
            return $meld[0]->getTileType()->isDragon();
        });
        $pairCount = $dragonMeldList->toFilteredTypesMeldList([PairMeldType::getInstance()])->count();
        $tripleOrQuadCount = $dragonMeldList->toFilteredTypesMeldList([TripleMeldType::getInstance(), QuadMeldType::getInstance()])->count();

        list($requiredPairCount, $requiredTripleOrQuadCount) = $isBig ? [0, 3] : [1, 2];
        return $pairCount == $requiredPairCount
        && $tripleOrQuadCount == $requiredTripleOrQuadCount;
    }

    function isFourWinds($isBig) {
        $this->assertCompleteCount();

        $windMeldList = $this->toFilteredMeldList(function (Meld $meld) {
            return $meld[0]->getTileType()->isWind();
        });
        $pairCount = $windMeldList->toFilteredTypesMeldList([PairMeldType::getInstance()])->count();
        $tripleOrQuadCount = $windMeldList->toFilteredTypesMeldList([TripleMeldType::getInstance(), QuadMeldType::getInstance()])->count();

        list($requiredPairCount, $requiredTripleOrQuadCount) = $isBig ? [0, 4] : [1, 3];
        return $pairCount == $requiredPairCount
        && $tripleOrQuadCount == $requiredTripleOrQuadCount;
    }
}