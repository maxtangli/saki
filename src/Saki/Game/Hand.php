<?php

namespace Saki\Game;

use Saki\Command\CommandProvided;
use Saki\Game\Meld\MeldList;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Util\Immutable;

/**
 * A hand for a player.
 *
 * nouns
 * - public : 13-style tileList. Used in: waiting analyze.
 * - target : targetData, fromSelf or fromOther.
 * - private: public + targetData which mustExist. Error if targetData not exist. Used in: win analyze.
 * - melded: meldList.
 * - complete: private + melded.toTileList. Used in: some yaku analyze?
 *
 * @package Saki\Hand
 */
class Hand implements Immutable {
    private $public;
    private $melded;
    private $target;

    /**
     * @param TileList $public
     * @param MeldList $melded
     * @param Target $target
     */
    function __construct(TileList $public, MeldList $melded, Target $target) {
        $this->public = $public->getCopy();
        $this->melded = $melded->getCopy();
        $this->target = $target;

        if (!$this->isPublicComplete()) {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('target[%s],public[%s],melded[%s]', $this->getTarget(), $this->getPublic(), $this->getMelded());
    }

    /**
     * @param SeatWind $actor
     * @param ArrayList $commandList
     * @param bool $mayViewHand
     * @return array
     */
    function toJson(SeatWind $actor, ArrayList $commandList, bool $mayViewHand) {
        $melded = $this->getMelded()->toJson();
        if ($mayViewHand) {
            $toTileData = function (Tile $tile) use ($commandList, $actor) {
                $discardCommand = "discard $actor $tile";
                $command = $commandList->valueExist($discardCommand)
                    ? $discardCommand
                    : null;
                return [
                    'tile' => $tile->__toString(),
                    'command' => $command,
                ];
            };
            $public = $this->getPublic()
                ->orderByTileID()
                ->toArray($toTileData);
            $target = $this->getTarget()->existAndIsCreator($actor)
                ? $toTileData($this->getTarget()->getTile())
                : ['tile' => 'X', 'command' => null];
        } else {
            $public = array_fill(0, $this->getPublic()->count(), ['tile' => 'O', 'command' => null]);
            $target = $this->getTarget()->existAndIsCreator($actor)
                ? ['tile' => 'O', 'command' => null]
                : ['tile' => 'X', 'command' => null];
        }
        return [
            'public' => $public,
            'melded' => $melded,
            'target' => $target,
        ];
    }

    /**
     * @param TileList|null $public
     * @param MeldList|null $melded
     * @param Tile|null $targetTile
     * @return Hand
     */
    function toHand(TileList $public = null, MeldList $melded = null, Tile $targetTile = null) {
        $validTargetTile = $targetTile === null
            || $this->getTarget()->getTile() == $targetTile
            || $this->getTarget()->getType()->isOwnByCreator(); // currently public-target-tile-set is not allowed 
        if (!$validTargetTile) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid argument [%s],[%s],[%s] for current $target[%s].',
                    $public, $melded, $targetTile, $this->getTarget()
                )
            );
        }

        $newPublic = $public ?? $this->getPublic();
        $newMelded = $melded ?? $this->getMelded();
        $newTarget = $targetTile ?
            $this->getTarget()->toSetValue($targetTile) : // validate exist
            $this->getTarget();
        $newHand = new Hand($newPublic, $newMelded, $newTarget);
        return $newHand;
    }

    /**
     * @param TileList $replace
     * @return Hand
     */
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
        return $this->public->getCopy();
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
            ->insertLast($targetTile);
    }

    /**
     * @return MeldList
     */
    function getMelded() {
        return $this->melded->getCopy();
    }

    /**
     * @return bool
     */
    function isConcealed() {
        return $this->getMelded()->isConcealed();
    }

    /**
     * @return bool
     */
    protected function isPublicComplete() {
        return $this->getPublic()->count()
        + $this->getMelded()->getNormalizedTileCount()
        == 13;
    }

    /**
     * @return bool
     */
    function isComplete() {
        return $this->isPublicComplete()
        && $this->getTarget()->exist();
    }

    /**
     * @return TileList
     */
    function getComplete() {
        return $this->getPrivate()->getCopy()// validate complete
        ->concat($this->getMelded()->toTileList());
    }

    /**
     * @param Hand $other
     * @return bool
     */
    function samePhase(Hand $other) {
        return $this->getTarget()->exist()
        == $other->getTarget()->exist();
    }
}