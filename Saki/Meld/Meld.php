<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Util\ArrayLikeObject;

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

    private $tileReadonlyOrderedList;
    private $meldType;
    private $exposed;

    /**
     * @param TileList $tileList
     * @param $meldType
     * @param bool $exposed
     */
    function __construct(TileList $tileList, MeldType $meldType = null, $exposed = true) {
        if ($meldType !== null && !$meldType->valid($tileList)) {
            throw new \InvalidArgumentException();
        }
        $actualMeldType = $meldType !== null ? $meldType : self::getMeldTypeAnalyzer()->analyzeMeldType($tileList);
        $validConcealed = $exposed || ($actualMeldType instanceof TripleMeldType || $actualMeldType instanceof QuadMeldType);
        if (!$validConcealed) {
            throw new \InvalidArgumentException(sprintf('Invalid argument $exposed[%s] for $actualMeldType[%s].', $exposed, $actualMeldType));
        }

        $tileOrderedList = new TileSortedList($tileList->toArray());
        parent::__construct($tileOrderedList->toArray());
        $this->tileReadonlyOrderedList = $tileOrderedList;
        $this->meldType = $actualMeldType;
        $this->exposed = $exposed;
    }

    function __toString() {
        $s = $this->tileReadonlyOrderedList->__toString();
        return $this->isConcealed() ? "($s)" : $s;
    }

    function equals(Meld $other, $compareExposed = true) {
        return $this->tileReadonlyOrderedList == $other->tileReadonlyOrderedList
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
            new Meld($this->tileReadonlyOrderedList, $this->getMeldType(), $exposedFlag);
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

    function isLowestSideRun($exposedFlag = null) {
        return $this->isRun($exposedFlag) && $this->getFirst()->getNumber() == 1;
    }

    function isHighestSideRun($exposedFlag = null) {
        return $this->isRun($exposedFlag) && $this->getFirst()->getNumber() == 9;
    }

    function isLowestOrHighestSideRun($exposedFlag = null) {
        return $this->isLowestSideRun($exposedFlag) || $this->isHighestSideRun($exposedFlag);
    }

    function isTriple($exposedFlag = null) {
        return $this->getMeldType() instanceof TripleMeldType && $this->matchExposed($exposedFlag);
    }

    function isQuad($exposedFlag = null) {
        return $this->getMeldType() instanceof QuadMeldType && $this->matchExposed($exposedFlag);
    }

    function isTripleOrQuad($exposedFlag = null) {
        return $this->isTriple($exposedFlag) || $this->isQuad($exposedFlag);
    }

    function isHandWinSet() {
        return $this->isRun() || $this->isTriple();
    }

    function isWinSet() {
        return $this->isRun() || $this->isTriple() || $this->isQuad();
    }

    // weak MeldType

    function isSingle() {
        return $this->getMeldType() instanceof SingleMeldType;
    }

    function isWeakRun() {
        return $this->getMeldType() instanceof WeakRunMeldType;
    }

    function isSingleOrWeakRun() {
        return $this->isSingle() || $this->isWeakRun();
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

        $waitingTiles = $this->getMeldType()->getWaitingTiles($this->tileReadonlyOrderedList);
        return in_array($tile, $waitingTiles);
    }

    function toTargetMeld(Tile $tile, MeldType $targetMeldType = null, $exposedFlag = null) {
        if (!$this->canToTargetMeld($tile, $targetMeldType)) {
            throw new \InvalidArgumentException();
        }

        $targetTileList = new TileSortedList(array_merge($this->tileReadonlyOrderedList->toArray(), [$tile]));
        $actualTargetMeldType = $this->getActualTargetMeldType($targetMeldType);
        $targetExposed = $exposedFlag !== null ? $exposedFlag : $this->isExposed();
        return new Meld($targetTileList, $actualTargetMeldType, $targetExposed);
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

