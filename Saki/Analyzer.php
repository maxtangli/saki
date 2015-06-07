<?php

namespace Saki;

use Saki\TileList;

class Analyzer {

    function getMeldCompositions(TileList $tileList, array $meldTypes) {
        $tiles = iterator_to_array($tileList);

        $resultAryMelds = array();
        foreach ($meldTypes as $meldType) {
            $candidateTiles = array_slice($tiles, 0, $meldType->getTileCount());
            if ($meldType->valid($candidateTiles)) {
                $firstMeld = $candidateTiles;
                $otherTiles = array_slice($tiles, $meldType->getTileCount());
                if (!empty($otherTiles)) {
                    $aryOtherMelds = $this->getMeldCompositionsImpl(new TileList($otherTiles), $meldTypes);
                    if (!empty($aryOtherMelds)) {
                        $aryMelds = array_map(function ($otherMelds) use ($firstMeld) {
                            return array_merge([$firstMeld], $otherMelds);
                        }, $aryOtherMelds);
                        $resultAryMelds = array_merge($resultAryMelds, $aryMelds);
                    }
                } else {
                    $aryMelds = [$firstMeld];
                    $resultAryMelds = array_merge($resultAryMelds, $aryMelds);
                }
            }
        }
        return $resultAryMelds;
    }
}