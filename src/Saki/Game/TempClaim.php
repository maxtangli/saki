<?php
namespace Saki\Game;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\MeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

/**
 * @package Saki\Game
 */
class TempClaim {
    private $toMeldType;
    private $toConcealed;
    private $fromTiles;
    private $otherTile;
    private $fromMeld;

    /**
     * @param MeldType $toMeldType
     * @param bool|null $toConcealed
     * @param array|null $fromTiles
     * @param Tile|null $otherTile
     * @param Meld|null $fromMeld
     */
    function __construct(MeldType $toMeldType, bool $toConcealed = null
        , array $fromTiles = null, Tile $otherTile = null, Meld $fromMeld = null) {
        $this->toMeldType = $toMeldType;
        $this->toConcealed = $toConcealed;
        $this->fromTiles = $fromTiles;
        $this->otherTile = $otherTile;
        $this->fromMeld = $fromMeld;
    }

    /**
     * @return MeldType
     */
    function getToMeldType() {
        return $this->toMeldType;
    }

    /**
     * @return boolean
     */
    function getToConcealed() {
        return $this->toConcealed
        ?? $this->getFromMeldNullable()->isConcealed();
    }

    /**
     * @return array|null
     */
    function getFromTilesNullable() {
        return $this->fromTiles;
    }

    /**
     * @return null|Tile
     */
    function getOtherTileNullable() {
        return $this->otherTile;
    }

    /**
     * @return null|Meld
     */
    function getFromMeldNullable() {
        return $this->fromMeld;
    }

    /**
     * @return Tile[]
     */
    function getFromTiles() {
        return (new TileList($this->getFromTilesNullable() ?? []))
            ->insertLast($this->getOtherTileNullable() ?? [])
            ->toArray();
    }

    /**
     * chow          hand [],target         -> meld not concealed
     * pung          hand [],target         -> meld not concealed
     * kong          hand [],target         -> meld not concealed
     * concealedKong hand [],               -> meld concealed
     * extendKong           ,target,claimed -> meld keeps concealedFlag
     * @return Tile[]
     */
    function getToMeldTiles() {
        $fromMeld = $this->getFromMeldNullable();
        $fromMeldTiles = $fromMeld ? $fromMeld->toArray() : [];
        return (new TileList($this->getFromTiles()))
            ->insertLast($fromMeldTiles)
            ->toArray();
    }

    /**
     * @return Meld
     */
    function getToMeld() {
        return new Meld($this->getToMeldTiles(), $this->getToMeldType(), $this->getToConcealed());
    }

    /**
     * @param TileList $fromPrivate
     * @param MeldList $fromDeclare
     * @return bool
     */
    function valid(TileList $fromPrivate, MeldList $fromDeclare) {
        return $fromPrivate->valueExist($this->getFromTiles())
        && $fromDeclare->valueExist($this->getFromMeldNullable() ?? [], Meld::getEqual(false));
    }

    /**
     * @param TileList $fromPrivate
     * @return TileList
     */
    function getToPublic(TileList $fromPrivate) {
        return $fromPrivate->getCopy()
            ->remove($this->getFromTiles());
    }

    /**
     * @param MeldList $fromDeclare
     * @return MeldList
     */
    function getToDeclare(MeldList $fromDeclare) {
        return $fromDeclare->getCopy()
            ->remove($this->getFromMeldNullable() ?? [], Meld::getEqual(false))
            ->insertLast($this->getToMeld());
    }
}