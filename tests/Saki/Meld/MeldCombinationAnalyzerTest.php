<?php

use Saki\Meld\Meld;
use Saki\Meld\MeldListAnalyzer;
use Saki\Meld\RunMeldType;
use Saki\Tile\TileList;

class MeldListAnalyzerTest extends \SakiTestCase {
    /**
     * @dataProvider getMeldCompositionsProvider
     */
    function testGetMeldCompositions($expectedMeldListStrings, $tilesStr, $meldTypes) {
        $round = new MeldListAnalyzer($meldTypes, 0);
        $combinationList = $round->analyzeMeldListList(TileList::fromString($tilesStr));
        $actualMeldListStrings = array_map(function ($v) {
            return $v->__toString();
        }, $combinationList->toArray());
        $meldTypesStr = implode(',', $meldTypes);
        $msg = "\$tilesStr[$tilesStr] \$meldTypes[$meldTypesStr]";
        $this->assertSame($expectedMeldListStrings, $actualMeldListStrings, $msg);
    }

    function getMeldCompositionsProvider() {
        $meldTypes = [
            \Saki\Meld\PairMeldType::create(),
            \Saki\Meld\RunMeldType::create(),
            \Saki\Meld\TripleMeldType::create(),
            \Saki\Meld\QuadMeldType::create(),
        ];
        return [
            // empty case
            [[], '', $meldTypes],
            [[], '1s', $meldTypes],
            [[], '12s', $meldTypes],
            [[], '1s1p', $meldTypes],
            // one possibility
            [['(123s)'], '123s', $meldTypes],
            [['(123m),(123p)'], '123m123p', $meldTypes],
            [['(123m),(123p),(123s)'], '123m123p123s', $meldTypes],
            // multiple possibility
            [['(11s),(11s)', '(1111s)'], '1111s', $meldTypes],
            // not continue
            [['(11s),(22s),(33s)', '(123s),(123s)'], '112233s', $meldTypes],
        ];
    }

    function testConcealed() {
        $round = new MeldListAnalyzer([RunMeldType::create()], 0, true);
        $combinationList = $round->analyzeMeldListList(TileList::fromString('123s'));
        $this->assertEquals(Meld::fromString('(123s)'), $combinationList[0][0]);
    }
}