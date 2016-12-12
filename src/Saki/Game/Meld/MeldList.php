<?php
namespace Saki\Game\Meld;

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * A sequence of Meld.
 * @package Saki\Game\Meld
 */
class MeldList extends ArrayList {
    /**
     * @param string $s
     * @return bool
     */
    static function validString(string $s) {
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

    /**
     * @param array $meldTypes
     * @param bool|null $concealedFlag
     * @return \Closure
     */
    protected static function getPredicate(array $meldTypes, bool $concealedFlag = null) {
        return function (Meld $meld) use ($meldTypes, $concealedFlag) {
            return in_array($meld->getMeldType(), $meldTypes)
            && $meld->matchConcealed($concealedFlag);
        };
    }

    public $json; // temp solution to support display set toJson()

    /**
     * @param array|null $a
     */
    function __construct(array $a = null) {
        parent::__construct($a);
        $this->json = [];
    }

    function getCopy() {
        $copy = parent::getCopy();
        $copy->json = $this->json;
        return $copy;
    }

    /**
     * @return string[][]
     */
    function toJson() {
        return $this->json;

        $meldToStringArray = function (Meld $meld) {
            return $meld->toJson();
        };
        return $this->toArray($meldToStringArray);
    }

    /**
     * @return TileList
     */
    function toTileList() {
        return (new TileList())->fromSelectMany($this, function (Meld $meld) {
            return $meld->toArray();
        });
    }

    /**
     * @param array $targetMeldTypes
     * @param bool|null $concealedFlag
     * @return MeldList
     */
    function toFiltered(array $targetMeldTypes, bool $concealedFlag = null) {
        return $this->getCopy()->where($this->getPredicate($targetMeldTypes, $concealedFlag));
    }

    /**
     * @param bool $concealedFlag
     * @return $this
     */
    function toConcealed(bool $concealedFlag) {
        return (new self)->fromSelect($this, function (Meld $meld) use ($concealedFlag) {
            return $meld->toConcealed($concealedFlag);
        });
    }

    /**
     * @return bool
     */
    function isConcealed() {
        return $this->all(function (Meld $meld) {
            return $meld->isConcealed();
        });
    }

    /**
     * @return int
     */
    function getNormalizedTileCount() {
        // note: each kong introduces 1 extra Tile
        $tileCount = $this->getSum(function (Meld $meld) {
            return $meld->count();
        });
        $kongMeldCount = $this->toFiltered([KongMeldType::create()])->count();
        $n = $tileCount - $kongMeldCount;
        return $n;
    }

    /**
     * @return bool
     */
    function isCompletePrivateHandCount() {
        return $this->getNormalizedTileCount() == 14;
    }

    protected function assertCompletePrivateHandCount() {
        if (!$this->isCompletePrivateHandCount()) {
            throw new \LogicException();
        }
    }

    /**
     * @param Tile $tile
     * @return bool
     */
    function tileExist(Tile $tile) {
        return $this->any(function (Meld $meld) use ($tile) {
            return $meld->valueExist($tile);
        });
    }

    //region series
    /**
     * @return bool
     */
    function isSevenUniquePairs() {
        $this->assertCompletePrivateHandCount();
        $uniquePairCount = $this->toFiltered([PairMeldType::create()])->distinct()->count();
        return $uniquePairCount == 7;
    }

    /**
     * @return bool
     */
    function isFourWinSetAndOnePair() {
        $this->assertCompletePrivateHandCount();
        $winSetCount = $this->getCount(function (Meld $meld) {
            return $meld->getWinSetType()->isWinSet();
        });
        $pairCount = $this->getCount($this->getPredicate([PairMeldType::create()]));
        return [$winSetCount, $pairCount] == [4, 1];
    }

    /**
     * @return bool
     */
    function isFourChowAndOnePair() {
        $this->assertCompletePrivateHandCount();
        $chowCount = $this->getCount($this->getPredicate([ChowMeldType::create()]));
        $pairCount = $this->getCount($this->getPredicate([PairMeldType::create()]));
        return [$chowCount, $pairCount] == [4, 1];
    }

    /**
     * @param bool|false $requireConcealed
     * @return bool
     */
    function isFourPungsOrKongsAndAPair(bool $requireConcealed = false) {
        $this->assertCompletePrivateHandCount();

        $concealedFlag = $requireConcealed ? true : null;
        $isRequiredPungOrKong = $this->getPredicate([PungMeldType::create(), KongMeldType::create()], $concealedFlag);

        $pungOrKongCount = $this->getCount($isRequiredPungOrKong);
        $pairCount = $this->getCount($this->getPredicate([PairMeldType::create()]));
        return [$pungOrKongCount, $pairCount] == [4, 1];
    }
    //endregion

    // WARNING: be careful about compare of Tile.isRedDora, Meld.isConcealed.

    //region yaku: chow, three color, thirteen orphan
    /**
     * @param bool $isTwoDoubleChow
     * @return bool
     */
    function isDoubleChow(bool $isTwoDoubleChow) {
        $this->assertCompletePrivateHandCount();
        $requiredDoubleChowCount = $isTwoDoubleChow ? 2 : 1;

        $chowMeldList = $this->toFiltered([ChowMeldType::create()]);
        $getChowKey = function (Meld $chowMeld) {
            $considerConcealed = false;
            return $chowMeld->toSortedString($considerConcealed);
        };
        $doubleChowCount = count($chowMeldList->getCounts($getChowKey, 2));

        return $doubleChowCount >= $requiredDoubleChowCount;
    }

    /**
     * @return bool
     */
    function isPureStraight() {
        $this->assertCompletePrivateHandCount();
        $targetMeldsList = new ArrayList([
            [Meld::fromString('123m'), Meld::fromString('456m'), Meld::fromString('789m')],
            [Meld::fromString('123p'), Meld::fromString('456p'), Meld::fromString('789p')],
            [Meld::fromString('123s'), Meld::fromString('456s'), Meld::fromString('789s')],
        ]);
        $existInThis = function (array $targetMelds) {
            return $this->valueExist($targetMelds, Meld::getCompareKeySelector(false));
        };
        return $targetMeldsList->any($existInThis);
    }

    /**
     * @return bool
     */
    function isMixedTripleChow() {
        $this->assertCompletePrivateHandCount();
        $chowList = $this->toFiltered([ChowMeldType::create()]);
        return $chowList->isThreeColorSuits();
    }

    /**
     * @return bool
     */
    function isTriplePungOrKong() {
        $this->assertCompletePrivateHandCount();
        $suitPungOrKongList = $this->toFiltered([PungMeldType::create(), KongMeldType::create()])
            ->where(function (Meld $meld) {
                return $meld->isAllSuit();
            });
        return $suitPungOrKongList->isThreeColorSuits();
    }

    protected function isThreeColorSuits() {
        $map = []; // [1 => ['s' => true] ...]
        foreach ($this as $pungOrKong) {
            /** @var Tile $firstTile */
            $fistTile = $pungOrKong[0];
            $number = $fistTile->getNumber();
            $tileTypeString = $fistTile->getTileType()->__toString();
            $map[$number][$tileTypeString] = true;
            if (count($map[$fistTile->getNumber()]) == 3) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param bool $requirePairWaiting
     * @param Tile|null $targetTile
     * @return bool
     */
    function isThirteenOrphan(bool $requirePairWaiting, Tile $targetTile = null) {
        $this->assertCompletePrivateHandCount();

        $valid = !$requirePairWaiting || $targetTile !== null;
        if (!$valid) {
            throw new \InvalidArgumentException('Require targetTile for pure case.');
        }

        if ($this->count() != 1) {
            return false;
        }

        /** @var Meld $meld */
        $meld = $this[0];
        return $meld->isThirteenOrphan()
        && (!$requirePairWaiting || $meld->getCount(Utils::toPredicate($targetTile)) == 2);
    }
    //endregion

    //region yaku: pung and kong
    /**
     * @param Tile $valueTile
     * @return bool
     */
    function isValueTiles(Tile $valueTile) {
        $this->assertCompletePrivateHandCount();
        $pungOrKongList = $this->toFiltered([PungMeldType::create(), KongMeldType::create()]);
        $isValueMeld = function (Meld $pungOrKong) use ($valueTile) {
            /** @var Tile $firstTile */
            $firstTile = $pungOrKong[0];
            return $firstTile == $valueTile;
        };
        return $pungOrKongList->any($isValueMeld);
    }

    /**
     * @return bool
     */
    function isThreeConcealedPungsOrKongs() {
        $this->assertCompletePrivateHandCount();
        $isConcealedPungOrKong = $this->getPredicate([PungMeldType::create(), KongMeldType::create()], true);
        $concealedPungOrKongCount = $this->getCount($isConcealedPungOrKong);
        return $concealedPungOrKongCount == 3;
    }

    /**
     * @param bool $isFour
     * @return bool
     */
    function isThreeOrFourKongs(bool $isFour) {
        $this->assertCompletePrivateHandCount();
        $n = $isFour ? 4 : 3;
        $kongCount = $this->getCount($this->getPredicate([KongMeldType::create()]));
        return $kongCount == $n;
    }

    // yaku: tile concerned
    /**
     * @param bool $isPure
     * @return bool
     */
    function isOutsideHand(bool $isPure) {
        $this->assertCompletePrivateHandCount();

        $hasChow = $this->any($this->getPredicate([ChowMeldType::create()]));
        if (!$hasChow) {
            return false;
        }

        $isOutsideMeld = function (Meld $meld) use ($isPure) {
            return $isPure ? $meld->isAnyTerm() : $meld->isAnyTermOrHonour();
        };
        return $this->all($isOutsideMeld);
    }

    /**
     * @return bool
     */
    function isAllTerminals() {
        $this->assertCompletePrivateHandCount();
        $isAllTermMeld = function (Meld $meld) {
            return $meld->isAllTerm();
        };
        return $this->all($isAllTermMeld);
    }

    /**
     * @return bool
     */
    function isAllHonours() {
        $this->assertCompletePrivateHandCount();
        $isAllHonourMeld = function (Meld $meld) {
            return $meld->isAllHonour();
        };
        return $this->all($isAllHonourMeld);
    }

    /**
     * @return bool
     */
    function isAllTerminalsAndHonours() {
        $this->assertCompletePrivateHandCount();
        $isAllTermOrHonourMeld = function (Meld $meld) {
            return $meld->isAllTermOrHonour();
        };
        return $this->all($isAllTermOrHonourMeld);
    }

    /**
     * @param bool $isBig
     * @return bool
     */
    function isThreeDragon(bool $isBig) {
        $this->assertCompletePrivateHandCount();
        $dragonMeldList = $this->getCopy()->where(function (Meld $meld) {
            return $meld[0]->getTileType()->isDragon();
        });
        $pairCount = $dragonMeldList->getCount($this->getPredicate([PairMeldType::create()]));
        $pungOrKongCount = $dragonMeldList->getCount($this->getPredicate([PungMeldType::create(), KongMeldType::create()]));
        return [$pairCount, $pungOrKongCount] == ($isBig ? [0, 3] : [1, 2]);
    }

    /**
     * @param bool $isBig
     * @return bool
     */
    function isFourWinds(bool $isBig) {
        $this->assertCompletePrivateHandCount();
        $windMeldList = $this->where(function (Meld $meld) {
            return $meld[0]->getTileType()->isWind();
        });
        $pairCount = $windMeldList->getCount($this->getPredicate([PairMeldType::create()]));
        $pungOrKongCount = $windMeldList->getCount($this->getPredicate([PungMeldType::create(), KongMeldType::create()]));
        return [$pairCount, $pungOrKongCount] == ($isBig ? [0, 4] : [1, 3]);
    }
    //endregion
}