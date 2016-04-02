<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ValueObject;

/**
 * A not empty TileList under one MeldType.
 * @package Saki\Meld
 */
class Meld extends TileList implements ValueObject {
    private static $meldTypeAnalyzer;

    /**
     * @return MeldTypeAnalyzer
     */
    static function getMeldTypeAnalyzer() {
        self::$meldTypeAnalyzer = self::$meldTypeAnalyzer ?? new MeldTypeAnalyzer([
                // hand win set
                RunMeldType::getInstance(),
                TripleMeldType::getInstance(),
                // declare win set
                QuadMeldType::getInstance(),
                // pair
                PairMeldType::getInstance(),
                // weak
                WeakPairMeldType::getInstance(),
                WeakRunMeldType::getInstance(),
                // special
                ThirteenOrphanMeldType::getInstance(),
            ]);
        return self::$meldTypeAnalyzer;
    }

    /**
     * @param string $s
     * @return bool
     */
    static function validString(string $s) {
        // note: it's hard to implement by regex here since various MeldType exist.
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
    static function fromString(string $s) {
        $regex = sprintf('/^%s|(\(%s\))$/', TileList::REGEX_NOT_EMPTY_LIST, TileList::REGEX_NOT_EMPTY_LIST);
        if (preg_match($regex, $s) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid $s[%s] for Meld.', $s));
        }
        $concealed = $s[0] === '(';
        $tileListString = $concealed ? substr($s, 1, strlen($s) - 2) : $s;
        $tileList = TileList::fromString($tileListString); // validate
        return new static($tileList->toArray(), null, $concealed); // validate
    }

    /**
     * syntactic sugar.
     * @param bool $compareConcealed
     * @return \Closure
     */
    static function getEqual(bool $compareConcealed) {
        return function (Meld $a, Meld $b) use ($compareConcealed) {
            return $a->equalTo($b, $compareConcealed);
        };
    }

    private $meldType;
    private $concealed;

    function __construct(array $tiles, MeldType $meldType = null, bool $concealed = false) {
        parent::__construct($tiles);
        $this->orderByTileID();

        $actualMeldType = $meldType ?? self::getMeldTypeAnalyzer()->analyzeMeldType($this); // validate
        if (!$actualMeldType->valid($this)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $meldType[%s] for $tiles[%s].', $meldType, $this)
            );
        }

        $this->setWritable(false);
        $this->meldType = $actualMeldType;
        $this->concealed = $concealed;
    }

    function getCopy() {
        return new Meld($this->toArray(), $this->meldType, $this->concealed);
    }

    /**
     * @param Meld $other
     * @param bool $compareConcealed
     * @return bool
     */
    function equalTo(Meld $other, bool $compareConcealed) {
        return $this->toArray() == $other->toArray()
        && $this->meldType == $other->meldType
        && (!$compareConcealed || $this->concealed == $other->concealed);
    }

    function __toString() {
        return $this->toFormatString(true);
    }

    function toFormatString(bool $considerConcealed) {
        $s = parent::__toString();
        return $considerConcealed && $this->isConcealed() ? "($s)" : $s;
    }

    /**
     * @return TileList
     */
    function toTileList() {
        return new TileList($this->toArray());
    }

    /**
     * @param bool|null $concealedFlag
     * @return Meld
     */
    function toConcealed(bool $concealedFlag = null) {
        return $this->matchConcealed($concealedFlag) ? $this :
            new Meld($this->toArray(), $this->getMeldType(), $concealedFlag);
    }

    /**
     * @return MeldType|WeakMeldType
     */
    function getMeldType() {
        return $this->meldType;
    }

    /**
     * @return bool
     */
    function isConcealed() {
        return $this->concealed;
    }

    /**
     * @param bool|null $concealedFlag
     * @return bool
     */
    function matchConcealed(bool $concealedFlag = null) {
        return $concealedFlag === null || $this->isConcealed() === $concealedFlag;
    }

    //region MeldType delegates
    /**
     * @return bool
     */
    function isPair() {
        return $this->getMeldType() instanceof PairMeldType;
    }

    /**
     * @return bool
     */
    function isRun() {
        return $this->getMeldType() instanceof RunMeldType;
    }

    /**
     * @return bool
     */
    function isTriple(bool $concealedFlag = null) {
        return $this->getMeldType() instanceof TripleMeldType && $this->matchConcealed($concealedFlag);
    }

    /**
     * @return bool
     */
    function isQuad(bool $concealedFlag = null) {
        return $this->getMeldType() instanceof QuadMeldType && $this->matchConcealed($concealedFlag);
    }

    /**
     * @return bool
     */
    function isTripleOrQuad(bool $concealedFlag = null) {
        return $this->isTriple($concealedFlag) || $this->isQuad($concealedFlag);
    }

    /**
     * @return bool
     */
    function isWeakPair() {
        return $this->getMeldType() instanceof WeakPairMeldType;
    }

    /**
     * @return bool
     */
    function isWeakRun() {
        return $this->getMeldType() instanceof WeakRunMeldType;
    }

    /**
     * @return bool
     */
    function isThirteenOrphan() {
        return $this->getMeldType() instanceof ThirteenOrphanMeldType;
    }
    
    /**
     * @return WinSetType
     */
    function getWinSetType() {
        return $this->getMeldType()->getWinSetType();
    }
    //endregion

    //region target of weak meld type
    /**
     * @param Tile $waitingTile
     * @return bool
     */
    function canToWeakMeld(Tile $waitingTile) {
        if (!$this->valueExist($waitingTile)) {
            return false;
        }

        $weakMeldTileList = $this->toTileList()->remove($waitingTile);
        $weakMeldType = $this->getMeldTypeAnalyzer()->analyzeMeldType($weakMeldTileList, true);
        if ($weakMeldType === false) {
            return false;
        }

        $weakMeld = new Meld($weakMeldTileList->toArray(), $weakMeldType, $this->isConcealed());
        return $weakMeld->canToTargetMeld($waitingTile, $this->getMeldType());
    }

    /**
     * @param Tile $waitingTile
     * @return Meld
     */
    function toWeakMeld(Tile $waitingTile) {
        if (!$this->canToWeakMeld($waitingTile)) {
            throw new \InvalidArgumentException();
        }

        $weakMeldTileList = $this->toTileList()->remove($waitingTile);
        $weakMeld = new Meld($weakMeldTileList->toArray(), null, $this->isConcealed());
        return $weakMeld;
    }
    //endregion

    //region weak meld type
    /**
     * @param Tile $newTile
     * @param MeldType|null $targetMeldType
     * @return bool
     */
    function canToTargetMeld(Tile $newTile, MeldType $targetMeldType = null) {
        if (!$this->getMeldType()->hasTargetMeldType()) {
            return false;
        }

        if ($targetMeldType !== null
            && $targetMeldType != $this->getMeldType()->getTargetMeldType()
        ) {
            return false;
        }

        $waitingTileList = $this->getMeldType()->getWaitingTileList($this);
        return $waitingTileList->valueExist($newTile);
    }

    /**
     * @param Tile $newTile
     * @param MeldType|null $targetMeldType
     * @param bool|null $concealedFlag
     * @return Meld
     */
    function toTargetMeld(Tile $newTile, MeldType $targetMeldType = null, bool $concealedFlag = null) {
        if (!$this->canToTargetMeld($newTile, $targetMeldType)) {
            throw new \InvalidArgumentException();
        }

        $targetTileList = $this->toTileList()->insertLast($newTile)->orderByTileID();
        $actualTargetMeldType = $targetMeldType ?? $this->getMeldType()->getTargetMeldType();
        $targetConcealed = $concealedFlag ?? $this->isConcealed();
        return new Meld($targetTileList->toArray(), $actualTargetMeldType, $targetConcealed);
    }

    /**
     * @return TileList
     */
    function getWaitingTileList() {
        return $this->getMeldType()->getWaitingTileList($this);
    }

    /**
     * @return WaitingType
     */
    function getWaitingType() {
        return $this->getMeldType()->getWaitingType($this);
    }
    //endregion
}

