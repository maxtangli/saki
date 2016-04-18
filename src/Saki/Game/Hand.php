<?php

namespace Saki\Game;

use Saki\Meld\MeldList;
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
 * - publicPlusSelfTarget: public + targetData if fromSelf. Used in: debugSetHand. todo: any way to remove?
 * - declare: declared meldList.
 * - privatePlusDeclare: private + declare.toTileList. Used in: some yaku analyze? todo better way
 *
 * @package Saki\Hand
 */
class Hand implements Immutable { // todo facade better?
    /**
     * @param TileList $private
     * @param MeldList $declare
     * @return Hand
     */
    static function debugFromPrivate(TileList $private, MeldList $declare) {
        $public = $private->getCopy()->removeLast();
        $target = new Target(
            $private->getLast(),
            TargetType::create(TargetType::KEEP),
            SeatWind::createEast()
        );
        return new self($public, $declare, $target);
    }

    private $public;
    private $declare;
    private $target;

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

    /**
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