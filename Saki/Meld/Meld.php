<?php
namespace Saki\Meld;

use Saki\Tile;
use Saki\TileList;
use Saki\TileOrderedList;
use Saki\Util\ArrayLikeObject;

class Meld extends ArrayLikeObject {
    private static $meldTypeAnalyzer;

    /**
     * @return MeldTypeAnalyzer
     */
    static function getMeldTypeAnalyzer() {
        if (!isset(self::$meldTypeAnalyzer)) {
            self::$meldTypeAnalyzer = new MeldTypeAnalyzer();
        }
        return self::$meldTypeAnalyzer;
    }

    static function setMeldTypeAnalyzer(MeldTypeAnalyzer $meldTypeAnalyzer) {
        self::$meldTypeAnalyzer = $meldTypeAnalyzer;
    }

    static function validString($s) {
        $tileList = TileList::fromString($s);
        return static::getMeldTypeAnalyzer()->analyzeMeldType($tileList, true) !== false;
    }

    /**
     * @param string $s
     * @return Meld
     */
    static function fromString($s) {
        $tileList = TileList::fromString($s);
        return new static($tileList);
     }

    private $tileReadonlyOrderedList;
    private $meldType;

    /**
     * @param TileList $tileList
     * @param $meldType
     */
    function __construct(TileList $tileList, MeldType $meldType = null) {
        if ($meldType !== null && !$meldType->valid($tileList)) {
            throw new \InvalidArgumentException();
        }
        $actualMeldType = $meldType ?: self::getMeldTypeAnalyzer()->analyzeMeldType($tileList);

        $tileOrderedList = new TileOrderedList($tileList->toArray(), true);
        parent::__construct($tileOrderedList->toArray());
        $this->tileReadonlyOrderedList = $tileOrderedList;
        $this->meldType = $actualMeldType;
    }

    function getTileReadonlyOrderedList() {
        return $this->tileReadonlyOrderedList;
    }

    function getMeldType() {
        return $this->meldType;
    }

    function __toString() {
        return $this->getTileReadonlyOrderedList()->__toString();
    }

    function canAddKong(Tile $tile) {
        return $this->getMeldType() instanceof TripletMeldType && $this[0] == $tile;
    }

    /**
     * @param Tile $tile
     * @return Meld
     */
    function getAddedKongMeld(Tile $tile) {
        if (!$this->canAddKong($tile)) {
            throw new \InvalidArgumentException("Invalid addKong \$tile[$tile] for Meld \$this[$this]");
        }
        $newTileList = new TileList($this->toArray());
        $newTileList->add($tile);
        return new Meld($newTileList, KongMeldType::getInstance());
    }

    /**
     * @return Tile[]
     */
    public function toArray() {
        return parent::toArray();
    }

    /**
     * @param int $offset
     * @return Tile
     */
    function offsetGet($offset) {
        return parent::offsetGet($offset);
    }
}

