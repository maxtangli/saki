<?php

use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Win\TileSeries;
use Saki\Win\WaitingType;

class TileSeriesTest extends PHPUnit_Framework_TestCase {
    function testFourRunAndOnePair() {
        $s = \Saki\Win\TileSeries\FourRunAndOnePairTileSeries::getInstance();
        $allMeldList = MeldList::fromString('123s,456s,789s,123s,EE');

        $this->assertTrue($s->existIn($allMeldList));
        $this->assertFalse($s->existIn(MeldList::fromString('111s,123s,123s,123s,11s')));
    }

    /**
     * @dataProvider waitingTypeProvider
     */
    function testFourRunAndOnePairWaitingType($meldListString, $tileString, $expectedWaitingTypeValue) {
        $s = \Saki\Win\TileSeries\FourWinSetAnd1PairTileSeries::getInstance();
        $allMeldList = MeldList::fromString($meldListString);
        $winTile = Tile::fromString($tileString);
        $expected = WaitingType::getInstance($expectedWaitingTypeValue);
        $actual = $s->getWaitingType($allMeldList, $winTile);
        $this->assertEquals($expected, $actual, "[$meldListString],[$tileString]");

        $analyzer = new TileSeries\TileSeriesAnalyzer();
        $this->assertEquals($expected, $analyzer->analyzeWaitingType($allMeldList, $winTile));
    }

    function waitingTypeProvider() {
        return [ // todo more tests
            ['123s,456s,789s,111s,11s', '9s', WaitingType::TWO_SIDE_RUN_WAITING],
            ['123s,567s,789s,111s,11s', '7s', WaitingType::TWO_SIDE_RUN_WAITING],

            ['123s,456s,789s,EEE,WW', 'E', WaitingType::TWO_PONG_WAITING],

            ['123s,456s,789s,111s,11s', '7s', WaitingType::ONE_SIDE_RUN_WAITING],

            ['123s,456s,789s,111s,11s', '8s', WaitingType::MIDDLE_RUN_WAITING],

            ['123s,456s,789s,111s,EE', 'E', WaitingType::SINGLE_PAIR_WAITING],
        ];
    }

    function testSevenPairs() {
        $s = TileSeries\SevenPairsTileSeries::getInstance();
        $this->assertTrue($s->existIn(MeldList::fromString('11s,22s,33s,44s,55s,66s,77s')));
        $this->assertFalse($s->existIn(MeldList::fromString('11s,11s,33s,44s,55s,66s,77s')));
        $expectedWaitingType = WaitingType::getInstance(WaitingType::SINGLE_PAIR_WAITING);
        $allMeldList = MeldList::fromString('11s,22s,33s,44s,55s,66s,77s');
        $winTile = Tile::fromString('1s');
        $this->assertEquals($expectedWaitingType,
            $s->getWaitingType($allMeldList, $winTile));

        $analyzer = new TileSeries\TileSeriesAnalyzer();
        $this->assertEquals($expectedWaitingType, $analyzer->analyzeWaitingType($allMeldList, $winTile));
    }
}