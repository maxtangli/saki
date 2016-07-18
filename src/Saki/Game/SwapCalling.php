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
     * @param bool $allow
     */
    function __construct(bool $allow) {
        $this->allow = $allow;
    }

    /**
     * @return boolean
     */
    function allow() {
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

        if ($this->allow()) {
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
        if ($this->allow()) {
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