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
     * @param Area $area
     * @param Turn $turn
     * @param Tile[] $tiles
     * @param MeldType $meldType
     * @param Target $otherTarget
     * @return Claim
     */
    static function createPublic(Area $area, Turn $turn, array $tiles, MeldType $meldType, Target $otherTarget) {
        $toMeld = Meld::valid($tiles, $meldType)
            ? new Meld($tiles, $meldType)
            : null;
        return new self($area, $turn, $toMeld, null, $otherTarget);
    }

    /**
     * @param Area $area
     * @param Turn $turn
     * @param Tile $targetTile
     * @param Meld $fromMeld
     * @return Claim
     */
    static function createExtendKong(Area $area, Turn $turn, Tile $targetTile, Meld $fromMeld) {
        $toMeld = $fromMeld->canToTargetMeld($targetTile)
            ? $fromMeld->toTargetMeld($targetTile)
            : null;
        return new self($area, $turn, $toMeld, $fromMeld);
    }

    /**
     * @param Area $area
     * @param Turn $turn
     * @param Tile[] $tiles
     * @param MeldType $meldType
     * @return Claim
     */
    static function createConcealedKong(Area $area, Turn $turn, array $tiles, MeldType $meldType) {
        $toMeld = Meld::valid($tiles, $meldType, true)
            ? new Meld($tiles, $meldType, true)
            : null;
        return new self($area, $turn, $toMeld);
    }

    private $area;
    private $turn;
    private $toMeld;
    private $fromMeld;
    private $otherTile;
    private $fromRelation;

    /**
     * @param Area $area
     * @param Turn $turn
     * @param Meld $toMeld
     * @param Meld|null $fromMeld
     * @param Target $otherTarget
     */
    protected function __construct(Area $area, Turn $turn, Meld $toMeld = null,
                                   Meld $fromMeld = null, Target $otherTarget = null) {
        $this->area = $area;
        $this->turn = $turn;
        $this->toMeld = $toMeld;
        $this->fromMeld = $fromMeld;
        $this->otherTile = isset($otherTarget) ? $otherTarget->getTile() : null;
        $this->fromRelation = isset($otherTarget) ? $otherTarget->getRelation($area->getSeatWind()) : Relation::createSelf();
    }

    /**
     * @return array
     */
    function toJson() {
        // todo split into sub classes
        $l = $this->getToMeld()->toArrayList();
        if ($this->isChowOrPungOrKong()) {
            // move target tile to relation position
            $relationIndex = $this->getRelationIndex();
            $otherTileIndex = $l->getIndex($this->otherTile, Tile::getPrioritySelector());
            $a = $l->move($otherTileIndex, $relationIndex)
                ->toArray(Utils::getToStringCallback());
            $a[$relationIndex] = '-' . $a[$relationIndex];
            return $a;
        } elseif ($this->isExtendKong()) {
            $melded = $this->getArea()->getHand()->getMelded();
            $fromMeldIndex = $melded->getIndex($this->getFromMeldOrNull());
            $json = $melded->json[$fromMeldIndex];

            foreach ($json as $relationIndex => $tileJson) {
                if ($tileJson[0] == '-') {
                    break;
                }
            }
            if (!isset($relationIndex)) {
                throw new \LogicException();
            }
            $newIndex = $relationIndex + 1;

            $targetTile = $this->getToMeld()->toTileList()
                ->remove($this->getFromMeldOrNull()->toArray(), Tile::getPrioritySelector())
                ->getSingle();
            $targetTileJson = '-' . $targetTile;
            array_splice($json, $newIndex, 0, $targetTileJson);
            return $json;
        } elseif ($this->isConcealedKong()) {
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
     * @return bool
     */
    function isChow() {
        return $this->getToMeld()->isChow();
    }

    /**
     * @return bool
     */
    function isPung() {
        return $this->getToMeld()->isPung();
    }

    /**
     * @return bool
     */
    function isKong() {
        return $this->getToMeld()->isKong(false)
            && !$this->isExtendKong();
    }

    /**
     * @return bool
     */
    function isChowOrPung() {
        return $this->isChow() || $this->isPung();
    }

    /**
     * @return bool
     */
    function isChowOrPungOrKong() {
        return $this->isChow() || $this->isPung() || $this->isKong();
    }

    /**
     * @return bool
     */
    function isExtendKong() {
        return isset($this->fromMeld);
    }

    /**
     * @return bool
     */
    function isConcealedKong() {
        return $this->getToMeld()->isKong(true);
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
        return $this->getArea()->getSeatWind();
    }

    /**
     * @return Area
     */
    function getArea() {
        return $this->area;
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
     * @return bool
     */
    function valid() {
        // params ok
        if (!$this->validToMeld()) {
            return false;
        }

        $area = $this->getArea();
        $toMeld = $this->getToMeld();
        $round = $area->getRound();
        $hand = $area->getHand();

        // phaseState allow claim
        if (!$round->getPhaseState()->allowClaim()) {
            return false;
        }

        // chow, pong, kong commands
        if ($this->isChowOrPungOrKong()) {
            // require not riichi
            if ($area->getRiichiStatus()->isRiichi()) {
                return false;
            }

            // require wall remain
            if ($round->getWall()->getDrawWall()->isEmpty()) {
                return false;
            }
        }

        // chow commands require SwapCalling.executable
        $swapCalling = $round->getRule()->getSwapCalling();
        if ($this->isChow()
            && !$swapCalling->allowChow($hand->getPublic(), $hand->getTarget()->getTile(), $toMeld)
        ) {
            return false;
        }

        // kong commands require ableDrawReplacement
        if ($this->isKong()
            && !$round->getWall()->getReplaceWall()->ableOutNext()
        ) {
            return false;
        }

        // concealedKong after riichi require
        if ($this->isConcealedKong() && $area->getRiichiStatus()->isRiichi()) {
            // 1. concealedKong tiles contains target tile
            $targetTile = $hand->getTarget()->getTile();
            $concealedKongContainTargetTile = $this->getToMeld()->toTileList()
                ->valueExist($targetTile);
            if (!$concealedKongContainTargetTile) {
                return false;
            }

            // 2. waiting tiles not change after concealedKong
            $waitingAnalyzer = $round->getRule()->getWinAnalyzer()->getWaitingAnalyzer();
            $currentWaiting = $waitingAnalyzer->analyzePublic($hand->getPublic(), $hand->getMelded());

            $newPublic = $hand->getPrivate()->remove($this->getToMeld()->toArray());
            $newMelded = $hand->getMelded()->insertLast($this->getToMeld());
            $newWaiting = $waitingAnalyzer->analyzePublic($newPublic, $newMelded);

            $sameWaiting = ($currentWaiting == $newWaiting);
            if (!$sameWaiting) {
                return false;
            }
        }

        // able to create meld
        $validHand = $hand->getPrivate()->valueExist($this->getFromTiles(), Tile::getPrioritySelector()) // handle red
            && $hand->getMelded()->valueExist($this->getFromMeldOrNull() ?? [], Meld::getCompareKeySelector(true, true)); // handle red
        if (!$validHand) {
            return false;
        }

        return true;
    }

    function apply() {
        if (!$this->valid()) {
            throw new \InvalidArgumentException();
        }

        $area = $this->getArea();
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

        if (!$hand->getTarget()->isCreator($this->getActor())) {
            $round->getTurnHolder()->getOpenHistory()
                ->setLastDiscardDeclared();
        }

        $round->getTurnHolder()->getClaimHistory()
            ->recordClaim($this->getTurn());
    }
}