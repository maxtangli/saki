<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;

class ThirteenOrphanMeldType extends MeldType {
    private static $allTileLists;

    function getTileCount() {
        return 14;
    }

    protected function validFaces(TileList $validCountTileList) {
//        $target = $validCountTileList->getCopy()->orderByTileID(); // error todo better design of getCopy()
        $target = (new TileList())->fromSelect($validCountTileList)->orderByTileID();
        return $this->getAllPossibleTileLists()->any(function (TileList $tileList) use ($target) {
            return $tileList->__toString() == $target->__toString();
        });
    }

    function getPossibleTileLists(Tile $firstTile) {
        if ($firstTile != Tile::fromString('1m')) {
            return [];
        }
        return $this->getAllPossibleTileLists();
    }

    function getWinSetType() {
        return WinSetType::create(WinSetType::SPECIAL);
    }

    /**
     * @return ArrayList An ArrayList of TileLists.
     */
    protected function getAllPossibleTileLists() {
        if (self::$allTileLists === null) {
            $a = [
                '119m19p19sESWNCPF', '199m19p19sESWNCPF',
                '19m119p19sESWNCPF', '19m199p19sESWNCPF',
                '19m19p119sESWNCPF', '19m19p199sESWNCPF',
                '19m19p19sEESWNCPF', '19m19p19sESSWNCPF', '19m19p19sESWWNCPF', '19m19p19sESWNNCPF',
                '19m19p19sESWNCCPF', '19m19p19sESWNCPPF', '19m19p19sESWNCPFF',
            ];
            self::$allTileLists = (new ArrayList($a))->select(function (string $s) {
                return TileList::fromString($s);
            });
        }
        return self::$allTileLists;
    }
}