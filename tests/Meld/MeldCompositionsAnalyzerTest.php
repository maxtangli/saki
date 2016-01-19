<?php

use Saki\Meld\Meld;
use Saki\Meld\MeldCompositionsAnalyzer;
use Saki\Meld\RunMeldType;
use Saki\Tile\TileList;

class MeldCompositionsAnalyzerTest extends PHPUnit_Framework_TestCase {
    /**
     * @dataProvider getMeldCompositionsProvider
     */
    function testGetMeldCompositions($expectedMeldListStrings, $tilesStr, $meldTypes) {
        $r = new \Saki\Meld\MeldCompositionsAnalyzer();
        $meldLists = $r->analyzeMeldCompositions(\Saki\Tile\TileList::fromString($tilesStr), $meldTypes, 0, false);
        $actualMeldListStrings = array_map(function ($v) {
            return $v->__toString();
        }, $meldLists);
        $meldTypesStr = implode(',', $meldTypes);
        $msg = "\$tilesStr[$tilesStr] \$meldTypes[$meldTypesStr]";
        $this->assertSame($expectedMeldListStrings, $actualMeldListStrings, $msg);
    }

    function getMeldCompositionsProvider() {
        $meldTypes = [
            \Saki\Meld\PairMeldType::getInstance(),
            \Saki\Meld\RunMeldType::getInstance(),
            \Saki\Meld\TripleMeldType::getInstance(),
            \Saki\Meld\QuadMeldType::getInstance(),
        ];
        return [
            // empty case
            [[], '', $meldTypes],
            [[], '1s', $meldTypes],
            [[], '12s', $meldTypes],
            [[], '1s1p', $meldTypes],
            // one possibility
            [['123s'], '123s', $meldTypes],
            [['123m,123p'], '123m123p', $meldTypes],
            [['123m,123p,123s'], '123m123p123s', $meldTypes],
            // multiple possibility
            [['11s,11s', '1111s'], '1111s', $meldTypes],
            // not continue
            [['11s,22s,33s', '123s,123s'], '112233s', $meldTypes],
        ];
    }

    function testConcealed() {
        $r = new MeldCompositionsAnalyzer();
        $meldLists = $r->analyzeMeldCompositions(TileList::fromString('123s'), [RunMeldType::getInstance()], 0, true);
        $this->assertEquals(Meld::fromString('(123s)'), $meldLists[0][0]);
        $meldLists = $r->analyzeMeldCompositions(TileList::fromString('123s'), [RunMeldType::getInstance()], 0, false);
        $this->assertEquals(Meld::fromString('123s'), $meldLists[0][0]);
    }

    function testPerformance() {
        $tileListString = '112233s';
        $r = new \Saki\Meld\MeldCompositionsAnalyzer();
        $meldTypes = [
            \Saki\Meld\PairMeldType::getInstance(),
            \Saki\Meld\RunMeldType::getInstance(),
            \Saki\Meld\TripleMeldType::getInstance(),
        ];
        $tileList = \Saki\Tile\TileList::fromString($tileListString);
        $time = microtime(true);
        $actual = $r->analyzeMeldCompositions($tileList, $meldTypes, 0, false);
        $cost = microtime(true) - $time;
        $costMs = round($cost * 1000);
        //echo sprintf('analyzeMeldCompositions time cost: %s ms.', $costMs);

        $expected = ['11s,22s,33s', '123s,123s'];
        $this->assertEquals($expected, $actual, sprintf('[%s],[%s]',implode(' or ',$expected), implode(' or ',$actual)));
    }
}