<?php
namespace Saki\Game;

use Saki\Game\Meld\Meld;
use Saki\Game\Meld\MeldType;
use Saki\Game\Tile\Tile;
use Saki\Util\Immutable;
use Saki\Util\Utils;

/**
 * @package Saki\Game
 */
class Claim implements Immutable {
    /**
     * @param SeatWind $actor
     * @param Turn $turn
     * @param Tile[] $tiles
     * @param MeldType $meldType
     * @param Target $otherTarget
     * @return Claim
     */
    static function createPublic(SeatWind $actor, Turn $turn, array $tiles, MeldType $meldType, Target $otherTarget) {
        $toMeld = Meld::valid($tiles, $meldType)
            ? new Meld($tiles, $meldType)
            : null;
        return new self($actor, $turn, $toMeld, null, $otherTarget);
    }

    /**
     * @param SeatWind $actor
     * @param Turn $turn
     * @param Tile $targetTile
     * @param Meld $fromMeld
     * @return Claim
     */
    static function createExtendKong(SeatWind $actor, Turn $turn, Tile $targetTile, Meld $fromMeld) {
        $toMeld = $fromMeld->canToTargetMeld($targetTile)
            ? $fromMeld->toTargetMeld($targetTile)
            : null;
        return new self($actor, $turn, $toMeld, $fromMeld);
    }

    /**
     * @param SeatWind $actor
     * @param Turn $turn
     * @param Tile[] $tiles
     * @param MeldType $meldType
     * @return Claim
     */
    static function createConcealedKong(SeatWind $actor, Turn $turn, array $tiles, MeldType $meldType) {
        $toMeld = Meld::valid($tiles, $meldType, true)
            ? new Meld($tiles, $meldType, true)
            : null;
        return new self($actor, $turn, $toMeld);
    }

    private $actor;
    private $turn;
    private $toMeld;
    private $fromMeld;
    private $otherTile;
    private $fromRelation;

    /**
     * @param SeatWind $actor
     * @param Turn $turn
     * @param Meld $toMeld
     * @param Meld|null $fromMeld
     * @param Target $otherTarget
     */
    protected function __construct(SeatWind $actor, Turn $turn, Meld $toMeld = null,
                                   Meld $fromMeld = null, Target $otherTarget = null) {
        $this->actor = $actor;
        $this->turn = $turn;
        $this->toMeld = $toMeld;
        $this->fromMeld = $fromMeld;
        $this->otherTile = isset($otherTarget) ? $otherTarget->getTile() : null;
        $this->fromRelation = isset($otherTarget) ? $otherTarget->getRelation($actor) : Relation::createSelf();
    }

    /**
     * @return array
     */
    function toJson() {
        $meld = $this->getToMeld();
        $l = $meld->toArrayList();
        $isExtendKong = isset($this->fromMeld);
        if ($meld->isChow() || $meld->isPung() || ($meld->isKong(false) && !$isExtendKong)) {
            // move target tile to relation position
            $relationIndex = $this->getRelationIndex();
            $otherTileIndex = $meld->getIndex($this->otherTile, Tile::getPrioritySelector());
            $a = $l->move($otherTileIndex, $relationIndex)
                ->toArray(Utils::getToStringCallback());
            $a[$relationIndex] = '-' . $a[$relationIndex];
            return $a;
        } elseif ($isExtendKong) {
            return $meld->toJson(); // todo
//            // insert target tile before fromClaim's relation position
//            $fromClaim = $this->getFromMeldOrNull()->getClaim();
//            $relationIndex = $fromClaim->getRelationIndex() + 1;
//            $a = $fromClaim->toJson();
//
//            $targetTile = $this->getToMeld()->toTileList()
//                ->remove($this->getFromMeldOrNull()->toArray(), Tile::getPrioritySelector())
//                ->getSingle();
//            $targetTileJson = '-' . $targetTile->__toString();
//
//            array_splice($a, $relationIndex, 0, $targetTileJson);
//            return $a;
        } elseif ($meld->isKong(true)) {
            // if contains 1 red, swap it to pos 2
            $isRed = function (Tile $tile) {
                return $tile->isRedDora();
            };
            $redTileList = $l->getCopy()->where($isRed);
            if ($redTileList->isNotEmpty()) {
                if ($redTileList->count() >= 2) {
                    throw new \LogicException('not implemented.');
                }
                $redTileIndex = $l->getIndex($redTileList->getSingle(), Tile::getPrioritySelector());
                $l->swap($redTileIndex, 2);
            }

            // hide first and last tile which means concealed meld
            $a = $l->toArray(Utils::getToStringCallback());
            $a[0] = $a[3] = 'O';
            return $a;
        } else {
            throw new \LogicException();
        }
    }

    /**
     * @return int
     */
    function getRelationIndex() {
        return $this->fromRelation->toDisplaySetIndex($this->getToMeld()->count());
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
    function getFromMeldOrNull() {
        return $this->fromMeld;
    }

    /**
     * @return Tile[]
     */
    function getFromMeldTiles() {
        return $this->fromMeld !== null ?
            $this->fromMeld->toArray() :
            [];
    }

    /**
     * @return Tile[]
     */
    function getFromTiles() {
        return $this->getToMeld()->toTileList()
            ->remove($this->getFromMeldTiles())
            ->toArray();
    }

    /**
     * Used in: SwapCalling
     * @return array
     */
    function getFromSelfTiles() {
        return $this->getToMeld()->toTileList()
            ->remove($this->getFromMeldTiles())
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
            // todo bug: should only apply for chow, pong, kong
            return false;
        }

        // chow commands require SwapCalling.executable
        // todo note: seems not good to place here
        $swapCalling = $round->getRule()->getSwapCalling();
        $validSwapCalling = !$toMeld->isChow()
            || $swapCalling->allowChow($hand->getPublic(), $hand->getTarget()->getTile(), $toMeld);
        if (!$validSwapCalling) {
            return false;
        }

        // kong commands require ableDrawReplacement
        // todo note: seems not good to place here
        $validDrawReplacementAble = !$toMeld->isKong()
            || $round->getWall()->getReplaceWall()->ableOutNext();
        if (!$validDrawReplacementAble) {
            return false;
        }

        // able to create meld
        $validHand = $hand->getPrivate()->valueExist($this->getFromTiles(), Tile::getPrioritySelector()) // handle red
            && $hand->getMelded()->valueExist($this->getFromMeldOrNull() ?? [], Meld::getCompareKeySelector(true, true)); // handle red
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
            $wall = $round->getWall();
            $newPublic = $newPrivateOrPublic;
            $newTargetTile = $wall->getReplaceWall()->outNext();
            $wall->getIndicatorWall()->openIndicator();
            $newTarget = new Target($newTargetTile, TargetType::create(TargetType::REPLACE), $this->getActor());
        } else {
            throw new \LogicException();
        }

        $newMelded = $hand->getMelded()->getCopy();
        $fromMeld = $this->getFromMeldOrNull();
        if (isset($fromMeld)) {
            $fromIndex = $newMelded->getIndex($fromMeld);
            $newMelded->replaceAt($fromIndex, $this->getToMeld());
            $newMelded->json[$fromIndex] = $this->toJson();
        } else {
            $newMelded->insertLast($this->getToMeld());
            $newMelded->json[] = $this->toJson();
        }
        $newHand = new Hand($newPublic, $newMelded, $newTarget);
        $area->setHand($newHand);

        if (!$hand->getTarget()->isOwner($this->getActor())) {
            $round->getOpenHistory()->setLastDiscardDeclared();
        }

        $round->getClaimHistory()->recordClaim($this->getTurn());
    }
}