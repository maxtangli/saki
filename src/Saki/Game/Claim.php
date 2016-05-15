<?php
namespace Saki\Game;

use Saki\Meld\Meld;
use Saki\Meld\MeldType;
use Saki\Tile\Tile;
use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class Claim implements Immutable {
    /**
     * @param SeatWind $actor
     * @param Turn $turn
     * @param Tile $tile
     * @param Meld $fromMelded
     * @return Claim
     */
    static function createFromMelded(SeatWind $actor, Turn $turn, Tile $tile, Meld $fromMelded) {
        $toMeld = $fromMelded->canToTargetMeld($tile)
            ? $fromMelded->toTargetMeld($tile)
            : null;
        return new self($actor, $turn, $toMeld, $fromMelded);
    }

    /**
     * @param SeatWind $actor
     * @param Turn $turn
     * @param array $tiles
     * @param MeldType $meldType
     * @param bool $concealed
     * @return Claim
     */
    static function create(SeatWind $actor, Turn $turn,
                           array $tiles, MeldType $meldType, bool $concealed) {
        $toMeld = Meld::valid($tiles, $meldType, $concealed)
            ? new Meld($tiles, $meldType, $concealed)
            : null;
        return new self($actor, $turn, $toMeld, null);
    }

    private $actor;
    private $turn;
    private $toMeld;
    private $fromMelded;

    /**
     * @param SeatWind $actor
     * @param Turn $turn
     * @param Meld $toMeld
     * @param Meld|null $fromMelded
     */
    protected function __construct(SeatWind $actor, Turn $turn, Meld $toMeld, Meld $fromMelded = null) {
        $this->actor = $actor;
        $this->turn = $turn;
        $this->toMeld = $toMeld;
        $this->fromMelded = $fromMelded;
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
     * @param Area $area
     * @return bool
     */
    function valid(Area $area) {
        $hand = $area->getHand();
        return $this->validToMeld()
        && $hand->getPrivate()->valueExist($this->getFromTiles())
        && $hand->getMelded()->valueExist($this->getFromMeldedOrNull() ?? []);
    }

    /**
     * @param Area $area
     */
    function apply(Area $area) {
        if (!$this->valid($area)) {
            throw new \InvalidArgumentException();
        }

        $areas = $area->getAreas();
        $hand = $area->getHand();

        $newPrivateOrPublic = $hand->getPrivate()->getCopy()
            ->remove($this->getFromTiles());
        if ($newPrivateOrPublic->getHandSize()->isPrivate()) {
            $newTargetTile = $newPrivateOrPublic->getLast();
            $newPublic = $newPrivateOrPublic->getCopy()->removeLast();
            $newTarget = new Target($newTargetTile, TargetType::create(TargetType::KEEP), $this->getActor());
        } elseif ($newPrivateOrPublic->getHandSize()->isPublic()) {
            $newPublic = $newPrivateOrPublic;
            $newTargetTile = $areas->getWall()
                ->drawReplacement();
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
            $areas->getOpenHistory()->setLastDiscardDeclared();
        }

        $areas->getClaimHistory()->recordClaim($this->getTurn());
    }
}