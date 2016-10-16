<?php
namespace Saki\Game;

use Saki\Meld\Meld;
use Saki\Meld\WeakRunMeldType;
use Saki\Phase\PrivatePhaseState;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
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
     * @param Meld $run
     * @return bool
     */
    function allowChow(TileList $public, Tile $targetTile, Meld $run) {
        if (!$run->isRun()) {
            throw new \InvalidArgumentException();
        }

        if ($this->allowSwapCalling()) {
            return true;
        }

        $chowWeakRun = $run->toWeakMeld($targetTile);
        $discardAble = function (Tile $tile) use ($chowWeakRun) {
            return $this->discardAble($chowWeakRun, $tile);
        };
        $afterChow = $public->getCopy()
            ->insertLast($targetTile)
            ->remove($run->toArray());
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
        if (!$claim->getToMeld()->isRun()) {
            return true;
        }

        $chowWeakRun = new Meld($claim->getFromSelfTiles(), WeakRunMeldType::create());
        return $this->discardAble($chowWeakRun, $tile);
    }

    /**
     * @param Meld $chowWeakRun
     * @param Tile $tile
     * @return bool
     */
    protected function discardAble(Meld $chowWeakRun, Tile $tile) {
        $ngTileList = $chowWeakRun->getWaiting();
        return !$ngTileList->valueExist($tile);
    }
}