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

    function getTileReadonlyOrderedList() {
        return $this->tileReadonlyOrderedList;
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

    function isPair() {
        return $this->getMeldType() instanceof PairMeldType;
    }

    /**
     * @return bool
     */
    function isRun() {
        return $this->getMeldType() instanceof RunMeldType;
    }

//    function isLowestSideRun() {
//        return $this->isRun() && $this->getFirst()->getNumber()==1;
//    }
//
//    function isHighestSideRun() {
//        return $this->isRun() && $this->getFirst()->getNumber()==9;
//    }
//
//    function isSideRun() {
//        return $this->isLowestSideRun() || $this->isHighestSideRun();
//    }

    function isTriple($exposedFlag = null) {
        return $this->getMeldType() instanceof TripleMeldType && ($exposedFlag === null || $this->isExposed() === $exposedFlag);
    }

    function isQuad($exposedFlag = null) {
        return $this->getMeldType() instanceof QuadMeldType && ($exposedFlag === null || $this->isExposed() === $exposedFlag);
    }

    function isTripleOrQuad($exposedFlag = null) {
        return $this->isTriple($exposedFlag) || $this->isQuad($exposedFlag);
    }

    /**
     * 面子
     * @return bool
     */
    function isWinSet() {
        return $this->isRun() || $this->isTriple() || $this->isQuad();
    }

    function __toString() {
        $s = $this->getTileReadonlyOrderedList()->__toString();
        return $this->isConcealed() ? "($s)" : $s;
    }

    function canPlusKong(Tile $tile) {
        return $this->getMeldType() instanceof TripleMeldType && $this[0] == $tile;
    }

    /**
     * @param Tile $tile
     * @param bool $forceExposed
     * @return Meld
     */
    function getPlusKongMeld(Tile $tile, $forceExposed) {
        if (!$this->canPlusKong($tile)) {
            throw new \InvalidArgumentException("Invalid addKong \$tile[$tile] for Meld \$this[$this]");
        }
        $newTileList = new TileList($this->toArray());
        $newTileList->push($tile);
        $isExposed = $forceExposed ? true : $this->isExposed();
        return new Meld($newTileList, QuadMeldType::getInstance(), $isExposed);
    }

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

