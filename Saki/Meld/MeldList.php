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

    function toFilteredTypesMeldList(array $targetMeldTypes, $concealedFlag = null) {
        return $this->toFilteredMeldList(function (Meld $meld) use ($targetMeldTypes, $concealedFlag) {
            return in_array($meld->getMeldType(), $targetMeldTypes)
            && ($concealedFlag === null || $meld->isConcealed() == $concealedFlag);
        });
    }

    function toConcealed($concealedFlag) {
        return new self($this->toArray(function (Meld $meld) use ($concealedFlag) {
            return $meld->toConcealed($concealedFlag);
        }));
    }

    function isConcealed() {
        return $this->all(function(Meld $meld) {
            return $meld->isConcealed();
        });
    }

    function isCompleteCount() {
        $quadMeldCount = $this->toFilteredTypesMeldList([QuadMeldType::getInstance()])->count();
        $n = $this->toSortedTileList()->count() - $quadMeldCount;
        return $n == 14;
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

    // WARNING: be careful about Meld.$concealed , especially for ArrayLikeObject search operations.

    // yaku: run concerned

    function isDoubleRun($isTwoDoubleRun) {
        $this->assertCompleteCount();

        $requiredDoubleRunCount = $isTwoDoubleRun ? 2 : 1;

        $runMeldList = $this->toFilteredTypesMeldList([RunMeldType::getInstance()]);

        $uniqueRunMeldList = new MeldList($runMeldList->toArray());
        $uniqueRunMeldList->unique(Meld::getEqualsCallback(false));

        $isDoubleRun = function (Meld $runMeld) use ($runMeldList) {
            return $runMeldList->getEqualValueCount($runMeld, Meld::getEqualsCallback(false)) >= 2;
        };
        return $uniqueRunMeldList->getFilteredValueCount($isDoubleRun) >= $requiredDoubleRunCount;
    }

    function isThreeColorRuns() {
        $this->assertCompleteCount();

        return $this->any(function (Meld $meld) {
            return $meld->isRun() && $this->valueExist($meld->toAllSuitTypes(), Meld::getEqualsCallback(false));
        });
    }

    function isThreeColorTripleOrQuads() {
        $this->assertCompleteCount();

        $tripleOrQuadList = $this->toFilteredTypesMeldList([TripleMeldType::getInstance(), QuadMeldType::getInstance()]);

        $map = []; // [1 => ['s' => 's'] ...]
        foreach($tripleOrQuadList as $tripleOrQuad) {
            $tile = $tripleOrQuad[0];
            $map[$tile->getNumber()][$tile->getTileType()->__toString()] = true;
            if (count($map[$tile->getNumber()]) == 3) {
                return true;
            }
        }
        return false;
    }

    function isFullStraight() {
        $this->assertCompleteCount();

        $targetMeldsArray = new ArrayLikeObject([
            [Meld::fromString('123m'), Meld::fromString('456m'), Meld::fromString('789m')],
            [Meld::fromString('123p'), Meld::fromString('456p'), Meld::fromString('789p')],
            [Meld::fromString('123s'), Meld::fromString('456s'), Meld::fromString('789s')],
        ]);

        return $targetMeldsArray->any(function (array $targetMelds) {
            return $this->valueExist($targetMelds, Meld::getEqualsCallback(false));
        });
    }

    // yaku: triple/quad concerned

    function isThreeConcealedTripleOrQuads() {
        $this->assertCompleteCount();
        $concealedTripleOrQuadList = $this->toFilteredTypesMeldList(
            [TripleMeldType::getInstance(), QuadMeldType::getInstance()], true);
        return $concealedTripleOrQuadList->count() == 3;
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

        $runMeldList = $this->toFilteredTypesMeldList([RunMeldType::getInstance()]);
        if ($runMeldList->isEmpty()) {
            return false;
        }

        return $this->all(function (Meld $meld) use ($isPure) {
            return $meld->isAnyTerminalOrHonor($isPure);
        });
    }

    function isAllTerminals() {
        $this->assertCompleteCount();

        return $this->all(function (Meld $meld) {
            return $meld->isAllTerminal();
        });
    }

    function isAllHonors() {
        $this->assertCompleteCount();

        return $this->all(function (Meld $meld) {
            return $meld->isAllHonor();
        });
    }

    function isAllTerminalsAndHonors() {
        $this->assertCompleteCount();

        return $this->all(function (Meld $meld) {
            return $meld->isAllTerminalOrHonor();
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