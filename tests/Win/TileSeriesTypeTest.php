<?php

use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Win\TileSeries;
use Saki\Win\WaitingType;

class TileSeriesTypeTest extends PHPUnit_Framework_TestCase {
    /**
     * @dataProvider FourWinSetAndOnePairProvider
     */
    function testFourWinSetAndOnePair($meldListString, $tileString, $expectedWaitingTypeValue) {
        $s = TileSeries::getInstance(TileSeries::FOUR_WIN_SET_AND_ONE_PAIR);
        $allMeldList = MeldList::fromString($meldListString);
        $this->assertTrue($s->existIn($allMeldList), sprintf('[%s],[%s].', $allMeldList, $s));

        $winTile = Tile::fromString($tileString);
        $expectedWaitingType = WaitingType::getInstance($expectedWaitingTypeValue);
        $actualWaitingType = $s->getWaitingType($allMeldList, $winTile);
        $this->assertEquals($expectedWaitingType, $actualWaitingType, "[$meldListString],[$tileString] -> [$expectedWaitingType] but [$actualWaitingType].");

        $analyzer = new \Saki\Win\TileSeriesAnalyzer();
        $this->assertEquals($s, $analyzer->analyzeTileSeries($allMeldList));
        $this->assertEquals($expectedWaitingType, $analyzer->analyzeWaitingType($allMeldList, $winTile));
    }

    function FourWinSetAndOnePairProvider() {
        return [
            ['123s,456s,789s,111m,EE', 'W', WaitingType::NOT_WAITING],

            ['123s,456s,789s,111m,11s', '9s', WaitingType::TWO_SIDE_RUN_WAITING],
            ['123s,567s,789s,111m,11s', '7s', WaitingType::TWO_SIDE_RUN_WAITING],

            ['123s,456s,789s,EEE,WW', 'E', WaitingType::TRIPLE_WAITING],

            ['123s,456s,789s,111s,11s', '7s', WaitingType::ONE_SIDE_RUN_WAITING],

            ['123s,456s,789s,111s,11s', '8s', WaitingType::MIDDLE_RUN_WAITING],

            ['123s,456s,789s,111s,EE', 'E', WaitingType::PAIR_WAITING],
        ];
    }

    function testSevenPairs() {
        $s = TileSeries::getInstance(TileSeries::SEVEN_PAIRS);

        $this->assertTrue($s->existIn(MeldList::fromString('11s,22s,33s,44s,55s,66s,77s')));
        $this->assertFalse($s->existIn(MeldList::fromString('11s,11s,33s,44s,55s,66s,77s')));

        $expectedWaitingType = WaitingType::getInstance(WaitingType::PAIR_WAITING);
        $allMeldList = MeldList::fromString('11s,22s,33s,44s,55s,66s,77s');
        $winTile = Tile::fromString('1s');
        $this->assertEquals($expectedWaitingType, $s->getWaitingType($allMeldList, $winTile));

        $analyzer = new \Saki\Win\TileSeriesAnalyzer();
        $this->assertEquals($s, $analyzer->analyzeTileSeries($allMeldList));
        $this->assertEquals($expectedWaitingType, $analyzer->analyzeWaitingType($allMeldList, $winTile));
    }
}