<?php

namespace Saki;

use Saki\Meld\Meld;
use Saki\Meld\MeldType;
use Saki\Meld\MeldList;
class Analyzer {

    /**
     * @param TileList|TileSortedList $tileList
     * @param MeldType[] $meldTypes
     * @return MeldList[]
     */
    function getMeldCompositions(TileList $tileList, array $meldTypes) {
        /*
         * meldList(tiles) = all merge(a valid meld from begin, meldList(other tiles))
         */
        $actualTileList = $tileList instanceof TileSortedList ? $tileList : new TileSortedList($tileList->toArray());

        $allMeldLists = [];
        foreach ($meldTypes as $meldType) {
            list($beginTileList, $remainTileList) = $actualTileList->getCutInTwoTileLists($meldType->getTileCount());
            if ($meldType->valid($beginTileList)) {
                $firstMeld = new Meld($beginTileList, $meldType);
                if (count($remainTileList) > 0) {
                    $thisMeldLists = $this->getMeldCompositions($remainTileList, $meldTypes);
                    if (count($thisMeldLists) > 0) {
                        foreach($thisMeldLists as $meldList) {
                            $meldList->insert($firstMeld, 0);
                        }
                        $allMeldLists = array_merge($allMeldLists, $thisMeldLists);
                    }
                } else {
                    $thisMeldLists = [new MeldList([$firstMeld])];
                    $allMeldLists = array_merge($allMeldLists, $thisMeldLists);
                }
            }
        }
        return $allMeldLists;
    }
}