<?php
namespace Saki\Game;

use Saki\Tile\Tile;

/**
 * @package Saki\Game
 */
class Target {
    /**
     * @return Target
     */
    static function createNull() {
        $obj = new self(Tile::fromString('E'), TargetType::create(TargetType::KEEP), Tile::fromString('E'));
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
     * @param Tile $creatorWind
     */
    function __construct(Tile $tile, TargetType $targetType, Tile $creatorWind) {
        $this->tile = $tile;
        $this->type = $targetType;
        $this->creator = $creatorWind;
    }

    function __toString() {
        return $this->exist()
            ? sprintf('%s,%s,%s', $this->getTile(), $this->getType(), $this->getCreator())
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
     *
     */
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
    function isKingSTile() {
        return $this->getType()->getValue() == TargetType::REPLACEMENT;
    }

    /**
     * @return bool
     */
    function isRobQuadTile() {
        return $this->getType()->getValue() == TargetType::KONG;
    }

    /**
     * @return Tile
     */
    function getCreator() {
        $this->assertExist();
        return $this->creator;
    }

    /**
     * @param Tile $playerWind
     * @return bool
     */
    function isCreator(Tile $playerWind) {
        $this->assertExist();
        $playerWind->assertWind();
        return $this->getCreator() == $playerWind;
    }

    /**
     * @param Tile $playerWind
     * @return bool
     */
    function isOwner(Tile $playerWind) {
        $isCreator = $this->isCreator($playerWind); // validate isWind
        $isOwnByCreator = $this->getType()->isOwnByCreator();
        return $isCreator == $isOwnByCreator;
    }
}