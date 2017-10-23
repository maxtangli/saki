<?php

use Saki\Game\Meld\ChowMeldType;
use Saki\Game\Meld\KongMeldType;
use Saki\Game\Meld\Meld;
use Saki\Game\Meld\MeldListAnalyzer;
use Saki\Game\Meld\PairMeldType;
use Saki\Game\Meld\PungMeldType;
use Saki\Game\Tile\TileList;
use Saki\Util\Utils;

class MeldListAnalyzerTest extends \SakiTestCase {
    function testGetCompositionCount() {
        $analyzer = new MeldListAnalyzer([], 0);
        $this->assertEquals(1, $analyzer->getCompositionCount(1, 1));
        $this->assertEquals(1, $analyzer->getCompositionCount(1, 4));
        $this->assertEquals(9, $analyzer->getCompositionCount(9, 1));
        $this->assertEquals(5, $analyzer->getCompositionCount(2, 4));
//        $this->assertEquals(118800, $analyzer->getCompositionCount(9,14)); // slow

//        $this->assertEquals(405349, $analyzer->getCompositionCountSum()); // slow
    }

    /**
     * @dataProvider getMeldCompositionsProvider
     */
    function testAnalyzeMeldListList(array $expectedMeldListStrings, string $tileList, array $meldTypes) {
        $analyzer = new MeldListAnalyzer($meldTypes, 0);
        $meldListList = $analyzer->analyzeMeldListList(TileList::fromString($tileList));

        $actualMeldListStrings = $meldListList->toArray(Utils::getToStringCallback());
        $message = sprintf('$tileList[%s], $meldTypes[%s].', $tileList, implode(',', $meldTypes));
        $this->assertSame($expectedMeldListStrings, $actualMeldListStrings, $message);
    }

    function getMeldCompositionsProvider() {
        $meldTypes = [
            PairMeldType::create(),
            ChowMeldType::create(),
            PungMeldType::create(),
            KongMeldType::create(),
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
        $round = new MeldListAnalyzer([ChowMeldType::create()], 0, true);
        $combinationList = $round->analyzeMeldListList(TileList::fromString('123s'));
        $this->assertEquals(Meld::fromString('(123s)'), $combinationList[0][0]);
    }
}