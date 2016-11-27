<?php
namespace Saki\Game;

use Saki\Game\Meld\Meld;
use Saki\Game\Meld\MeldType;
use Saki\Game\Tile\Tile;
use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class Claim implements Immutable {
    /**
     * @param SeatWind $actor
     * @param Turn $turn
     * @param Tile $targetTile
     * @param Meld $fromMelded
     * @return Claim
     */
    static function createFromMelded(SeatWind $actor, Turn $turn, Tile $targetTile, Meld $fromMelded) {
        $toMeld = $fromMelded->canToTargetMeld($targetTile)
            ? $fromMelded->toTargetMeld($targetTile)
            : null;
        return new self($actor, $turn, $toMeld, $fromMelded);
    }

    /**
     * @param SeatWind $actor
     * @param Turn $turn
     * @param Tile[] $tiles
     * @param MeldType $meldType
     * @param bool $concealed
     * @param Tile $otherTile
     * @return Claim
     */
    static function create(SeatWind $actor, Turn $turn,
                           array $tiles, MeldType $meldType, bool $concealed,
                           Tile $otherTile = null) {
        $toMeld = Meld::valid($tiles, $meldType, $concealed)
            ? new Meld($tiles, $meldType, $concealed)
            : null;
        return new self($actor, $turn, $toMeld, null, $otherTile);
    }

    private $actor;
    private $turn;
    private $toMeld;
    private $fromMelded;
    private $otherTile; // todo remove?

    /**
     * @param SeatWind $actor
     * @param Turn $turn
     * @param Meld $toMeld
     * @param Meld|null $fromMelded
     * @param Tile $otherTile
     */
    protected function __construct(SeatWind $actor, Turn $turn, Meld $toMeld, Meld $fromMelded = null, Tile $otherTile = null) {
        $this->actor = $actor;
        $this->turn = $turn;
        $this->toMeld = $toMeld;
        $this->fromMelded = $fromMelded;
        $this->otherTile = $otherTile;
    }

    /**
     * @return SeatWind
     */
    function getActor() {
        return $this->actor;
    }

    /**
     * @return Turn
     */
    function getTurn() {
        return $this->turn;
    }

    /**
     * @return bool
     */
    function validToMeld() {
        return $this->toMeld !== null;
    }

    /**
     * @return Meld
     */
    function getToMeld() {
        if (!$this->validToMeld()) {
            throw new \BadMethodCallException();
        }
        return $this->toMeld;
    }

    /**
     * @return Meld|null
     */
    function getFromMeldedOrNull() {
        return $this->fromMelded;
    }

    /**
     * @return Tile[]
     */
    function getFromMeldedTiles() {
        return $this->fromMelded !== null ?
            $this->fromMelded->toArray() :
            [];
    }

    /**
     * @return Tile[]
     */
    function getFromTiles() {
        return $this->getToMeld()->toTileList()
            ->remove($this->getFromMeldedTiles())
            ->toArray();
    }

    /**
     * Used in: SwapCalling
     * @return array
     */
    function getFromSelfTiles() {
        return $this->getToMeld()->toTileList()
            ->remove($this->getFromMeldedTiles())
            ->remove($this->otherTile ?? [])
            ->toArray();
    }

    /**
     * @param Area $area
     * @return bool
     */
    function valid(Area $area) {
        // params ok
        if (!$this->validToMeld()) {
            return false;
        }

        $toMeld = $this->getToMeld();
        $round = $area->getRound();
        $hand = $area->getHand();

        // phaseState allow claim
        $allowClaim = $round->getPhaseState()->allowClaim();
        if (!$allowClaim) {
            return false;
        }

        // chow, pong, kong commands require not riichi
        if ($area->getRiichiStatus()->isRiichi()) {
            return false;
        }

        // chow commands require SwapCalling.executable
        // todo note: seems not good to place here
        $swapCalling = $round->getRule()->getSwapCalling();
        $validSwapCalling = !$toMeld->isRun()
            || $swapCalling->allowChow($hand->getPublic(), $hand->getTarget()->getTile(), $toMeld);
        if (!$validSwapCalling) {
            return false;
        }

        // kong commands require ableDrawReplacement
        // todo note: seems not good to place here
        $validDrawReplacementAble = !$toMeld->isQuad()
            || $round->getWall()->getDeadWall()->getReplacementWall()->ableDrawReplacement();
        if (!$validDrawReplacementAble) {
            return false;
        }

        // able to create meld
        $validHand = $hand->getPrivate()->valueExist($this->getFromTiles(), Tile::getPrioritySelector()) // handle red
            && $hand->getMelded()->valueExist($this->getFromMeldedOrNull() ?? [], Meld::getCompareKeySelector(true, true)); // handle red
        if (!$validHand) {
            return false;
        }

        return true;
    }

    /**
     * @param Area $area
     */
    function apply(Area $area) {
        if (!$this->valid($area)) {
            throw new \InvalidArgumentException();
        }

        $round = $area->getRound();
        $hand = $area->getHand();

        $newPrivateOrPublic = $hand->getPrivate()->getCopy()
            ->remove($this->getFromTiles(), Tile::getPrioritySelector());
        if ($newPrivateOrPublic->getSize()->isPrivate()) {
            $newTargetTile = $newPrivateOrPublic->getLast();
            $newPublic = $newPrivateOrPublic->getCopy()->removeLast();
            $newTarget = new Target($newTargetTile, TargetType::create(TargetType::KEEP), $this->getActor());
        } elseif ($newPrivateOrPublic->getSize()->isPublic()) {
            $deadWall = $round->getWall()->getDeadWall();
            $newPublic = $newPrivateOrPublic;
            $newTargetTile = $deadWall->getReplacementWall()->drawReplacement();
            $deadWall->openIndicator();
            $newTarget = new Target($newTargetTile, TargetType::create(TargetType::REPLACE), $this->getActor());
        } else {
            throw new \LogicException();
        }
        $newMelded = $hand->getMelded()->getCopy()
            ->remove($this->getFromMeldedOrNull() ?? [])
            ->insertLast($this->getToMeld());
        $newHand = new Hand($newPublic, $newMelded, $newTarget);

        $area->setHand($newHand);

        if (!$hand->getTarget()->isOwner($this->getActor())) {
            $round->getOpenHistory()->setLastDiscardDeclared();
        }

        $round->getClaimHistory()->recordClaim($this->getTurn());
    }
}