<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Util\ArrayLikeObject;

/**
 * ValueObject
 * @package Saki\Meld
 */
class Meld extends ArrayLikeObject {
    private static $meldTypeAnalyzer;

    /**
     * @return MeldTypeAnalyzer
     */
    static function getMeldTypeAnalyzer() {
        if (!isset(self::$meldTypeAnalyzer)) {
            $meldTypes = MeldTypesFactory::getInstance()->getAllMeldTypes();
            self::$meldTypeAnalyzer = new MeldTypeAnalyzer($meldTypes);
        }
        return self::$meldTypeAnalyzer;
    }

    static function validString($s) {
        try {
            static::fromString($s);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param string $s
     * @return Meld
     */
    static function fromString($s) {
        $regex = sprintf('/^%s|(\(%s\))$/', TileList::REGEX_NOT_EMPTY_LIST, TileList::REGEX_NOT_EMPTY_LIST);
        if (preg_match($regex, $s) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid $s[%s] for Meld.', $s));
        }
        $exposed = $s[0] !== '(';
        $tileListString = $exposed ? $s : substr($s, 1, strlen($s) - 2);
        $tileList = TileList::fromString($tileListString);
        return new static($tileList, null, $exposed);
    }

    private $tileSortedList;
    private $meldType;
    private $exposed;

    /**
     * @param TileList $tileList
     * @param $meldType
     * @param bool $exposed
     */
    function __construct(TileList $tileList, MeldType $meldType = null, $exposed = true) {
        $tileSortedList = new TileSortedList($tileList->toArray());
        if ($meldType !== null && !$meldType->valid($tileSortedList)) {
            throw new \InvalidArgumentException();
        }

        $actualMeldType = $meldType !== null ? $meldType : self::getMeldTypeAnalyzer()->analyzeMeldType($tileSortedList);
//        $validConcealed = $exposed || ($actualMeldType instanceof TripleMeldType || $actualMeldType instanceof QuadMeldType);
//        if (!$validConcealed) {
//            throw new \InvalidArgumentException(sprintf('Invalid argument $exposed[%s] for $actualMeldType[%s].', $exposed, $actualMeldType));
//        }

        parent::__construct($tileSortedList->toArray());
        $this->tileSortedList = $tileSortedList;
        $this->meldType = $actualMeldType;
        $this->exposed = $exposed;
    }

    function __toString() {
        $s = $this->tileSortedList->__toString();
        return $this->isConcealed() ? "($s)" : $s;
    }

    function toTileSortedList() {
        return new TileSortedList($this->tileSortedList->toArray());
    }

    function equals(Meld $other, $compareExposed = true) {
        return $this->tileSortedList == $other->tileSortedList
        && ($compareExposed || $this->exposed == $other->exposed);
    }

    function getMeldType() {
        return $this->meldType;
    }

    function isExposed() {
        return $this->exposed;
    }

    function isConcealed() {
        return !$this->isExposed();
    }

    function toExposed($exposedFlag = null) {
        return $this->matchExposed($exposedFlag) ? $this :
            new Meld($this->tileSortedList, $this->getMeldType(), $exposedFlag);
    }

    protected function matchExposed($exposedFlag = null) {
        return $exposedFlag === null || $this->isExposed() === $exposedFlag;
    }

    // basic MeldType

    function isPair() {
        return $this->getMeldType() instanceof PairMeldType;
    }

    function isRun($exposedFlag = null) {
        return $this->getMeldType() instanceof RunMeldType && $this->matchExposed($exposedFlag);
    }

//    function isLowestSideRun($exposedFlag = null) {
//        return $this->isRun($exposedFlag) && $this->getFirst()->getNumber() == 1;
//    }
//
//    function isHighestSideRun($exposedFlag = null) {
//        return $this->isRun($exposedFlag) && $this->getFirst()->getNumber() == 9;
//    }
//
//    function isLowestOrHighestSideRun($exposedFlag = null) {
//        return $this->isLowestSideRun($exposedFlag) || $this->isHighestSideRun($exposedFlag);
//    }

    function isTriple($exposedFlag = null) {
        return $this->getMeldType() instanceof TripleMeldType && $this->matchExposed($exposedFlag);
    }

    function isQuad($exposedFlag = null) {
        return $this->getMeldType() instanceof QuadMeldType && $this->matchExposed($exposedFlag);
    }

    function isTripleOrQuad($exposedFlag = null) {
        return $this->isTriple($exposedFlag) || $this->isQuad($exposedFlag);
    }

    function getWinSetType() {
        return $this->getMeldType()->getWinSetType();
    }

    // target of a weak MeldType

    function canToWeakMeld(Tile $waitingTile) {
        if (!$this->valueExist($waitingTile)) {
            return false;
        }

        $weakMeldTileSortedList = $this->toTileSortedList();
        $weakMeldTileSortedList->removeByValue($waitingTile);
        $weakMeldType = $this->getMeldTypeAnalyzer()->analyzeMeldType($weakMeldTileSortedList, true);
        if (!$weakMeldType) {
            return false;
        }

        $weakMeld = new Meld($weakMeldTileSortedList, $weakMeldType, $this->isExposed());
        return $weakMeld->getMeldType()->hasTargetMeldType()
            && $weakMeld->canToTargetMeld($waitingTile, $this->getMeldType());
    }

    function toWeakMeld(Tile $waitingTile) {
        if (!$this->canToWeakMeld($waitingTile)) {
            throw new \InvalidArgumentException();
        }

        $weakMeldTileSortedList = $this->toTileSortedList();
        $weakMeldTileSortedList->removeByValue($waitingTile);
        $weakMeld = new Meld($weakMeldTileSortedList, null, $this->isExposed());
        return $weakMeld;
    }

    function getFromWeakMeldWaitingType(Tile $waitingTile) {
        return $this->toWeakMeld($waitingTile)->getWaitingType();
    }

    // weak MeldType

    function isWeakPair() {
        return $this->getMeldType() instanceof WeakPairMeldType;
    }

    function isWeakRun() {
        return $this->getMeldType() instanceof WeakRunMeldType;
    }

    protected function getActualTargetMeldType(MeldType $targetMeldType = null) {
        if ($targetMeldType !== null) {
            $actualTargetMeldType = $targetMeldType;
        } else {
            if (!$this->getMeldType()->hasTargetMeldType()) {
                throw new \InvalidArgumentException();
            }
            $actualTargetMeldType = $this->getMeldType()->getTargetMeldType();
        }
        return $actualTargetMeldType;
    }

    function canToTargetMeld(Tile $tile, MeldType $targetMeldType = null) {
        $actualTargetMeldType = $this->getActualTargetMeldType($targetMeldType);
        if ($actualTargetMeldType != $this->getMeldType()->getTargetMeldType()) {
            return false;
        }

        $waitingTiles = $this->getMeldType()->getWaitingTiles($this->tileSortedList);
        return in_array($tile, $waitingTiles);
    }

    function toTargetMeld(Tile $tile, MeldType $targetMeldType = null, $exposedFlag = null) {
        if (!$this->canToTargetMeld($tile, $targetMeldType)) {
            throw new \InvalidArgumentException();
        }

        $targetTileList = new TileSortedList(array_merge($this->tileSortedList->toArray(), [$tile]));
        $actualTargetMeldType = $this->getActualTargetMeldType($targetMeldType);
        $targetExposed = $exposedFlag !== null ? $exposedFlag : $this->isExposed();
        return new Meld($targetTileList, $actualTargetMeldType, $targetExposed);
    }

    function getWaitingTiles() {
        return $this->getMeldType()->getWaitingTiles($this->tileSortedList);
    }

    function getWaitingType() {
        return $this->getMeldType()->getWaitingType($this->tileSortedList);
    }

    // ArrayLikeObject issues

    /**
     * @return \Saki\Tile\Tile[]
     */
    public function toArray() {
        return parent::toArray();
    }

    /**
     * @param int $offset
     * @return \Saki\Tile\Tile
     */
    function offsetGet($offset) {
        return parent::offsetGet($offset);
    }
}

