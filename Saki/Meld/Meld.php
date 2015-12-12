<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Tile\TileType;
use Saki\Util\ArrayLikeObject;
use Saki\Util\ValueObject;

/**
 * ValueObject
 * @package Saki\Meld
 */
class Meld extends ArrayLikeObject implements ValueObject {
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
        $concealed = $s[0] === '(';
        $tileListString = $concealed ? substr($s, 1, strlen($s) - 2) : $s;
        $tileList = TileList::fromString($tileListString);
        return new static($tileList, null, $concealed);
    }

    static function getEqualsCallback($compareConcealed) {
        return function (Meld $a, Meld $b) use ($compareConcealed) {
            return $a->equals($b, $compareConcealed);
        };
    }

    private $tileSortedList;
    private $meldType;
    private $concealed;

    /**
     * @param TileList $tileList
     * @param MeldType $meldType
     * @param bool $concealed
     */
    function __construct(TileList $tileList, MeldType $meldType = null, $concealed = false) {
        $tileSortedList = new TileSortedList($tileList->toArray());
        if ($meldType !== null && !$meldType->valid($tileSortedList)) {
            throw new \InvalidArgumentException();
        }

        $actualMeldType = $meldType !== null ? $meldType : self::getMeldTypeAnalyzer()->analyzeMeldType($tileSortedList);

        parent::__construct($tileSortedList->toArray());
        $this->tileSortedList = $tileSortedList;
        $this->meldType = $actualMeldType;
        $this->concealed = $concealed;
    }

    function __toString() {
        $s = $this->tileSortedList->__toString();
        return $this->isConcealed() ? "($s)" : $s;
    }

    function equals(Meld $other, $compareConcealed = true) {
        return $this->tileSortedList == $other->tileSortedList
        && (!$compareConcealed || ($this->concealed == $other->concealed));
    }

    function toTileSortedList() {
        return new TileSortedList($this->tileSortedList->toArray());
    }

    function toConcealed($concealedFlag = null) {
        return $this->matchConcealed($concealedFlag) ? $this :
            new Meld($this->tileSortedList, $this->getMeldType(), $concealedFlag);
    }

    protected function toOtherSuitType(TileType $suitType) {
        $valid = $this->tileSortedList->isAllSuit();
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf('Not-all-suit Meld[%s] is unable to convert into $suitType[%s].', $this, $suitType)
            );
        }

        $currentTileTypeString = $this[0]->getTileType()->__toString();
        $targetTileTypeString = $suitType->__toString();
        $currentMeldString = $this->__toString();
        $targetMeldString = str_replace($currentTileTypeString, $targetTileTypeString, $currentMeldString);
        return Meld::fromString($targetMeldString);
    }

    function toAllSuitTypes() {
        $suitTypeArray = new ArrayLikeObject(TileType::getSuitTypes());
        return $suitTypeArray->toArray(function (TileType $suitType) {
            return $this->toOtherSuitType($suitType);
        });
    }

    function getMeldType() {
        return $this->meldType;
    }

    function isConcealed() {
        return $this->concealed;
    }

    protected function matchConcealed($concealedFlag = null) {
        return $concealedFlag === null || $this->isConcealed() === $concealedFlag;
    }

    // basic MeldType

    function isPair() {
        return $this->getMeldType() instanceof PairMeldType;
    }

    function isRun() {
        return $this->getMeldType() instanceof RunMeldType;
    }

    function isTriple($concealedFlag = null) {
        return $this->getMeldType() instanceof TripleMeldType && $this->matchConcealed($concealedFlag);
    }

    function isQuad($concealedFlag = null) {
        return $this->getMeldType() instanceof QuadMeldType && $this->matchConcealed($concealedFlag);
    }

    function isTripleOrQuad($concealedFlag = null) {
        return $this->isTriple($concealedFlag) || $this->isQuad($concealedFlag);
    }

    function getWinSetType() {
        return $this->getMeldType()->getWinSetType();
    }

    // yaku concerned
    function isAnyTerminalOrHonor($isPure) {
        return $this->any(function (Tile $tile) use ($isPure) {
            return $isPure ? $tile->isTerminal() : $tile->isTerminalOrHonor();
        });
    }

    function isAllTerminal() {
        return $this->tileSortedList->isAllTerminal();
    }

    function isAllHonor() {
        return $this->tileSortedList->isAllHonor();
    }

    function isAllTerminalOrHonor() {
        return $this->tileSortedList->isAllTerminalOrHonor();
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

        $weakMeld = new Meld($weakMeldTileSortedList, $weakMeldType, $this->isConcealed());
        return $weakMeld->getMeldType()->hasTargetMeldType()
        && $weakMeld->canToTargetMeld($waitingTile, $this->getMeldType());
    }

    function toWeakMeld(Tile $waitingTile) {
        if (!$this->canToWeakMeld($waitingTile)) {
            throw new \InvalidArgumentException();
        }

        $weakMeldTileSortedList = $this->toTileSortedList();
        $weakMeldTileSortedList->removeByValue($waitingTile);
        $weakMeld = new Meld($weakMeldTileSortedList, null, $this->isConcealed());
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

    function toTargetMeld(Tile $tile, MeldType $targetMeldType = null, $concealedFlag = null) {
        if (!$this->canToTargetMeld($tile, $targetMeldType)) {
            throw new \InvalidArgumentException();
        }

        $targetTileList = new TileSortedList(array_merge($this->tileSortedList->toArray(), [$tile]));
        $actualTargetMeldType = $this->getActualTargetMeldType($targetMeldType);
        $targetConcealed = $concealedFlag !== null ? $concealedFlag : $this->isConcealed();
        return new Meld($targetTileList, $actualTargetMeldType, $targetConcealed);
    }

    function getWaitingTiles() {
        return $this->getMeldType()->getWaitingTiles($this->tileSortedList);
    }

    function getWaitingType() {
        return $this->getMeldType()->getWaitingType($this->tileSortedList);
    }

    // ArrayLikeObject issues
    /**
     * @param callable|null $selector
     * @return Tile[]
     */
    function toArray(callable $selector = null) {
        return parent::toArray($selector);
    }

    /**
     * @param int $offset
     * @return \Saki\Tile\Tile
     */
    function offsetGet($offset) {
        return parent::offsetGet($offset);
    }
}

