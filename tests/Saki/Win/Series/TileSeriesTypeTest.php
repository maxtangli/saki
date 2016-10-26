<?php

use Saki\Game\Meld\MeldList;
use Saki\Game\SeatWind;
use Saki\Game\SubHand;
use Saki\Game\Target;
use Saki\Game\TargetType;
use Saki\Game\Tile\Tile;
use Saki\Win\Series\Series;
use Saki\Win\Waiting\WaitingType;

class SeriesTypeTest extends \SakiTestCase {
    /**
     * @dataProvider FourWinSetAndOnePairProvider
     */
    function testFourWinSetAndOnePair(string $meldListString,
                                      string $tileString,
                                      int $expectedSeries,
                                      int $expectedWaitingTypeValue) {
        $series = Series::create($expectedSeries);
        $allMeldList = MeldList::fromString($meldListString);
        $this->assertTrue($series->existIn($allMeldList), sprintf('[%s],[%s].', $allMeldList, $series));

        $privateMeldList = MeldList::fromString($meldListString);
        $melded = new MeldList();
        $winTile = Tile::fromString($tileString);
        $target = new Target($winTile, TargetType::create(TargetType::KEEP), SeatWind::createEast());
        $subHand = new SubHand($privateMeldList, $melded, $target);

        $expectedWaitingType = WaitingType::create($expectedWaitingTypeValue);
        $actualWaitingType = $series->getWaitingType($subHand);
        $this->assertEquals($expectedWaitingType, $actualWaitingType, "[$meldListString],[$tileString] -> [$expectedWaitingType] but [$actualWaitingType].");
    }

    function FourWinSetAndOnePairProvider() {
        return [
            ['123s,456s,789s,111m,11s', '9s', Series::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::TWO_SIDE_RUN_WAITING, ['6s', '9s']],
            ['123s,567s,789s,111m,11s', '7s', Series::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::TWO_SIDE_RUN_WAITING, ['4s', '7s']],
            ['123s,567s,789s,777s,11s', '7s', Series::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::TWO_SIDE_RUN_WAITING, ['1s', '4s', '7s']],

            ['123s,456s,789s,EEE,WW', 'E', Series::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::TRIPLE_WAITING, ['E', 'W']],

            ['123s,456s,789s,111s,11s', '7s', Series::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::ONE_SIDE_RUN_WAITING, ['7s']],

            ['123s,456s,789s,111s,11s', '8s', Series::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::MIDDLE_RUN_WAITING, ['8s']],

            ['123s,456s,789s,111s,EE', 'E', Series::FOUR_WIN_SET_AND_ONE_PAIR, WaitingType::PAIR_WAITING, ['E']],

            // seven pairs
            ['11s,22s,33s,44s,55s,66s,77s', '1s', Series::SEVEN_PAIRS, WaitingType::PAIR_WAITING, ['1s']],
        ];
    }

    function testSevenPairsNotExist() {
        $series = Series::create(Series::SEVEN_PAIRS);
        $this->assertFalse($series->existIn(MeldList::fromString('11s,11s,33s,44s,55s,66s,77s')));
    }
}