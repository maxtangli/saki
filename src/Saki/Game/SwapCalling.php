<?php
namespace Saki\Game;

use Saki\Game\Meld\Meld;
use Saki\Game\Meld\WeakChowMeldType;
use Saki\Game\Phase\PrivatePhaseState;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\Immutable;

/**
 * 喰い替え
 * @package Saki\Game
 */
class SwapCalling implements Immutable {
    private $allow;

    /**
     * @param bool $allowSwapCalling
     */
    function __construct(bool $allowSwapCalling) {
        $this->allow = $allowSwapCalling;
    }

    /**
     * @return boolean
     */
    function allowSwapCalling() {
        return $this->allow;
    }

    /**
     * @param TileList $public
     * @param Tile $targetTile
     * @param Meld $chow
     * @return bool
     */
    function allowChow(TileList $public, Tile $targetTile, Meld $chow) {
        if (!$chow->isChow()) {
            throw new \InvalidArgumentException();
        }

        if ($this->allowSwapCalling()) {
            return true;
        }

        $chowWeakChow = $chow->toWeakMeld($targetTile);
        $discardAble = function (Tile $tile) use ($chowWeakChow) {
            return $this->discardAble($chowWeakChow, $tile);
        };
        $afterChow = $public->getCopy()
            ->insertLast($targetTile)
            ->remove($chow->toArray());
        return $afterChow->any($discardAble);
    }

    /**
     * @param PrivatePhaseState $privatePhaseState
     * @param Tile $tile
     * @return bool
     */
    function allowOpen(PrivatePhaseState $privatePhaseState, Tile $tile) {
        if ($this->allowSwapCalling()) {
            return true;
        }

        if (!$privatePhaseState->hasClaim()) {
            return true;
        }

        $claim = $privatePhaseState->getClaim();
        if (!$claim->getToMeld()->isChow()) {
            return true;
        }

        $chowWeakChow = new Meld($claim->getFromSelfTiles(), WeakChowMeldType::create());
        return $this->discardAble($chowWeakChow, $tile);
    }

    /**
     * @param Meld $chowWeakChow
     * @param Tile $tile
     * @return bool
     */
    protected function discardAble(Meld $chowWeakChow, Tile $tile) {
        $ngTileList = $chowWeakChow->getWaiting();
        return !$ngTileList->valueExist($tile);
    }
}