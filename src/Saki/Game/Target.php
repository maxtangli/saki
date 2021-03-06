<?php
namespace Saki\Game;

use Saki\Game\Tile\Tile;
use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class Target implements Immutable {
    /**
     * @return Target
     */
    static function createNull() {
        $obj = new self(Tile::fromString('E'), TargetType::create(TargetType::KEEP), SeatWind::createEast());
        $obj->tile = null;
        $obj->type = null;
        $obj->creator = null;
        return $obj;
    }

    private $tile;
    private $type;
    private $creator;

    /**
     * @param Tile $tile
     * @param TargetType $targetType
     * @param SeatWind $creator
     */
    function __construct(Tile $tile, TargetType $targetType, SeatWind $creator) {
        $this->tile = $tile;
        $this->type = $targetType;
        $this->creator = $creator;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->exist()
            ? sprintf('%s,%s,by %s', $this->getTile(), $this->getType(), $this->getCreator())
            : 'null.';
    }

    /**
     * @param Tile|null $newTile
     * @param TargetType|null $newType
     * @return Target
     */
    function toSetValue(Tile $newTile = null, TargetType $newType = null) {
        $this->assertExist();
        return new self(
            $newTile ?? $this->tile,
            $newType ?? $this->type,
            $this->creator
        );
    }

    /**
     * @return bool
     */
    function exist() {
        return $this->tile !== null;
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function existAndIsCreator(SeatWind $seatWind) {
        return $this->exist() && $this->isCreator($seatWind);
    }

    protected function assertExist() {
        if (!$this->exist()) {
            throw new \BadMethodCallException('Bad method call on null Target.');
        }
    }

    /**
     * @return Tile
     */
    function getTile() {
        $this->assertExist();
        return $this->tile;
    }

    /**
     * @return Tile[]
     */
    function getTilesMayEmpty() {
        return $this->exist() ? [$this->getTile()] : [];
    }

    /**
     * @return TargetType
     */
    function getType() {
        $this->assertExist();
        return $this->type;
    }

    /**
     * @return bool
     */
    function isAfterAKong() {
        return $this->getType()->getValue() == TargetType::REPLACE;
    }

    /**
     * @return bool
     */
    function isRobbingAKong() {
        return $this->getType()->getValue() == TargetType::KONG;
    }

    /**
     * @return SeatWind
     */
    function getCreator() {
        $this->assertExist();
        return $this->creator;
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function isCreator(SeatWind $seatWind) {
        $this->assertExist();
        return $this->getCreator() == $seatWind;
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function isOwner(SeatWind $seatWind) {
        $this->assertExist();
        return $this->isCreator($seatWind)
            == $this->getType()->isOwnByCreator();
    }

    /**
     * @param SeatWind $self
     * @return Relation
     */
    function getRelation(SeatWind $self) {
        return Relation::createByOther($this->getCreator(), $self);
    }
}