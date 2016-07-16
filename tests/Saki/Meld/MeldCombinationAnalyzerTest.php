<?php

use Saki\Meld\Meld;
use Saki\Meld\MeldListAnalyzer;
use Saki\Meld\PairMeldType;
use Saki\Meld\QuadMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Tile\TileList;
use Saki\Util\Utils;

class MeldListAnalyzerTest extends \SakiTestCase {
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
            RunMeldType::create(),
            TripleMeldType::create(),
            QuadMeldType::create(),
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