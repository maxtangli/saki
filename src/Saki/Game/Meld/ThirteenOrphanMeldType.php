<?php
namespace Saki\Game\Meld;

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Game\Meld
 */
class ThirteenOrphanMeldType extends MeldType {
    private $allPossibleTileLists;

    protected function __construct() {
        $a = [
            '119m19p19sESWNCPF', '199m19p19sESWNCPF',
            '19m119p19sESWNCPF', '19m199p19sESWNCPF',
            '19m19p119sESWNCPF', '19m19p199sESWNCPF',
            '19m19p19sEESWNCPF', '19m19p19sESSWNCPF', '19m19p19sESWWNCPF', '19m19p19sESWNNCPF',
            '19m19p19sESWNCCPF', '19m19p19sESWNCPPF', '19m19p19sESWNCPFF',
        ];
        $toTileList = function (string $s) {
            return TileList::fromString($s);
        };
        $this->allPossibleTileLists = (new ArrayList($a))->select($toTileList);
    }

    /**
     * @return ArrayList An ArrayList of TileLists.
     */
    protected function getAllPossibleTileLists() {
        return $this->allPossibleTileLists;
    }

    //region MeldType impl
    function getTileCount() {
        return 14;
    }

    protected function validFaces(TileList $validCountTileList) {
        $target = $validCountTileList->getCopy()->orderByTileID();
        $matchTarget = function (TileList $tileList) use ($target) {
            return $tileList->__toString() == $target->__toString();
        };
        return $this->getAllPossibleTileLists()->any($matchTarget);
    }

    protected function getPossibleTileLists(Tile $firstTile) {
        if ($firstTile != Tile::fromString('1m')) {
            return [];
        }
        return $this->getAllPossibleTileLists();
    }

    function getWinSetType() {
        return WinSetType::create(WinSetType::SPECIAL);
    }
    //endregion
}