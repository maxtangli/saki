<?php

namespace Saki\Game;

use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\Immutable;

/**
 * A hand for a player.
 *
 * nouns
 * - public : 13-style tileList. Used in: waiting analyze.
 * - target : targetData, fromSelf or fromOther.
 * - private: public + targetData which mustExist. Error if targetData not exist. Used in: win analyze.
 * - publicPlusTarget: public + targetData if exist. Used in: create meld. todo: any way to remove?
 * - declare: declared meldList.
 * - privatePlusDeclare: private + declare.toTileList. Used in: some yaku analyze? todo better way
 *
 * @package Saki\Hand
 */
class Hand implements Immutable {
    
    private $public;
    private $declare;
    private $target;

    /**
     * @param TileList $public
     * @param MeldList $declare
     * @param Target $target
     */
    function __construct(TileList $public, MeldList $declare, Target $target) {
        if (!$public->getHandSize()->isPublic()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $public[%s]. Other params: $declare[%s], $target[%s]'
                    , $public, $declare, $target)
            );
        }

        $this->public = $public->getCopy()->lock();
        $this->declare = $declare->getCopy()->lock();
        $this->target = $target;
    }

    /**
     * @param TileList|null $public
     * @param MeldList|null $declare
     * @param Tile|null $targetTile
     * @return Hand
     */
    function toHand(TileList $public = null, MeldList $declare = null, Tile $targetTile = null) {
        // todo allow public-phase target tile set
        $validTargetTile = $targetTile === null ||
            $this->getTarget()->getType()->isOwnByCreator();
        if (!$validTargetTile) {
            throw new \InvalidArgumentException();
        }

        $newPublic = $public ?? $this->getPublic();
        $newDeclare = $declare ?? $this->getDeclare();
        $newTarget = $targetTile ? 
            $this->getTarget()->toSetValue($targetTile) : // validate exist
            $this->getTarget();
        $newHand = new Hand($newPublic, $newDeclare, $newTarget);
        return $newHand;
    }

    function toMockHand(TileList $replace) {
        // public
        if ($replace->count() <= $this->getPublic()->count()) { 
            $replaceIndexes = range(0, $replace->count() - 1);
            $public = $this->getPublic()->getCopy()
                ->replaceAt($replaceIndexes, $replace->toArray());
            return $this->toHand($public);
        }
        
        // private
        if ($replace->count() == $this->getPublic()->count() + 1) {
            $public = $replace->getCopy()->removeLast();
            $targetTile = $replace->getLast();
            return $this->toHand($public, null, $targetTile);
        }

        throw new \InvalidArgumentException();
    }
    
    /**
     * @return TileList
     */
    function getPublic() {
        return $this->public;
    }

    /**
     * @return Target
     */
    function getTarget() {
        return $this->target;
    }

    /**
     * @return TileList
     */
    function getPrivate() {
        $targetTile = $this->getTarget()->getTile(); // validate exist
        return $this->getPublic()->getCopy()
            ->insertLast($targetTile)
            ->lock();
    }

    /** todo remove
     * @return TileList
     */
    function getPublicPlusTarget() {
        return $this->getPublic()->getCopy()
            ->insertLast($this->getTarget()->getTilesMayEmpty())
            ->lock();
    }

    /**
     * @return MeldList
     */
    function getDeclare() {
        return $this->declare;
    }

    /**
     * @return bool
     */
    function isConcealed() {
        return $this->getDeclare()->isConcealed();
    }

    /**
     * @return bool
     */
    function isPublicPlusDeclareComplete() {
        return $this->getPublic()->count()
        + $this->getDeclare()->getNormalizedTileCount()
        == 13;
    }

    /**
     * @return bool
     */
    function isPrivatePlusDeclareComplete() {
        return $this->isPublicPlusDeclareComplete()
        && $this->getTarget()->exist();
    }

    /**
     * @return TileList
     */
    function getPrivatePlusDeclare() {
        return $this->getPrivate()->getCopy()// validate complete
        ->concat($this->getDeclare()->toTileList())
            ->lock();
    }
}