<?php

use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Win\TileSeries;
use Saki\Win\WaitingType;

class TileSeriesTypeTest extends PHPUnit_Framework_TestCase {
    /**
     * @dataProvider FourWinSetAndOnePairProvider
     */
    function testFourWinSetAndOnePair($meldListString, $tileString, $expectedTileSeries, $expectedWaitingTypeValue, $expectedWaitingTileStrings) {
        $s = TileSeries::create($expectedTileSeries);
        $allMeldList = MeldList::fromString($meldListString);
        $this->assertTrue($s->existIn($allMeldList), sprintf('[%s],[%s].', $allMeldList, $s));

        $declaredMeldList = new MeldList();

        $winTile = Tile::fromString($tileString);

        $expectedWaitingType = WaitingType::create($expectedWaitingTypeValue);
        $actualWaitingType = $s->getWaitingType($allMeldList, $winTile, $declaredMeldList);
        $this->assertEquals($expectedWaitingType, $actualWaitingType, "[$meldListString],[$tileString] -> [$expectedWaitingType] but [$actualWaitingType].");

//        $expectedWaitingTiles = array_map(function ($s) {
//            return Tile::fromString($s);
//        }, $expectedWaitingTileStrings);
//        $actualWaitingTiles = $s->getWaitingTileList($allMeldList, $winTile, $declaredMeldList)->toArray();
//        $this->assertEquals($expectedWaitingTiles, $actualWaitingTiles,
//            sprintf("[$meldListString],[$tileString] -> [%s] but [%s].", implode(',', $expectedWaitingTiles), implode(',', $actualWaitingTiles)));

        $analyzer = new \Saki\Win\TileSeriesAnalyzer();
        $this->assertEquals($s, $analyzer->analyzeTileSeries($allMeldList));
    }

    function FourWinSetAndOnePairProvider() {
        return [
            ['123s,456s,789s,111m,EE', 'W', TileSeries::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::NOT_WAITING, []],

            ['123s,456s,789s,111m,11s', '9s', TileSeries::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::TWO_SIDE_RUN_WAITING, ['6s', '9s']],
            ['123s,567s,789s,111m,11s', '7s', TileSeries::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::TWO_SIDE_RUN_WAITING, ['4s', '7s']],
            ['123s,567s,789s,777s,11s', '7s', TileSeries::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::TWO_SIDE_RUN_WAITING, ['1s', '4s', '7s']],

            ['123s,456s,789s,EEE,WW', 'E', TileSeries::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::TRIPLE_WAITING, ['E', 'W']],

            ['123s,456s,789s,111s,11s', '7s', TileSeries::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::ONE_SIDE_RUN_WAITING, ['7s']],

            ['123s,456s,789s,111s,11s', '8s', TileSeries::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::MIDDLE_RUN_WAITING, ['8s']],

            ['123s,456s,789s,111s,EE', 'E', TileSeries::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::PAIR_WAITING, ['E']],

            // seven pairs
            ['11s,22s,33s,44s,55s,66s,77s', '1s', TileSeries::SEVEN_PAIRS, WaitingType::PAIR_WAITING, ['1s']],
        ];
    }

    function testSevenPairsNotExist() {
        $s = TileSeries::create(TileSeries::SEVEN_PAIRS);
        $this->assertFalse($s->existIn(MeldList::fromString('11s,11s,33s,44s,55s,66s,77s')));
    }
}