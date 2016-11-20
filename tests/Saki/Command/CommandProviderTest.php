<?php

namespace tests\Command;

use Saki\Command\CommandProvider;
use Saki\Command\CommandSet;
use Saki\Game\SeatWind;
use Saki\Util\Utils;

class CommandProviderTest extends \SakiTestCase {
    /**
     * @param string[] $expected
     * @param bool $contains
     * @param string $actor
     */
    protected function assertExecutableList(array $expected, bool $contains, string $actor) {
        $provider = new CommandProvider($this->getCurrentRound(), CommandSet::createStandard());
        $executableList = $provider->provideActorAll(SeatWind::fromString($actor));

        foreach ($executableList as $executable) {
            if (!$executable->executable()) {
                throw new \LogicException(
                    sprintf('Command[%s] not executable.', $executable)
                );
            }
        }

        $strings = $executableList->toArray(Utils::getToStringCallback());

        if ($contains) {
            foreach ($expected as $s) {
                $this->assertContains($s, $strings);
            }
        } else {
            foreach ($expected as $s) {
                $this->assertNotContains($s, $strings);
            }
        }
    }

    function testAllExecutableList() {
//        $round = $this->getInitRound();
//        $provider = new CommandProvider($round, CommandSet::createStandard());
//
//        $all = $provider->getAllExecutableList(); // todo slow +0.3s
//        $this->assertNotEmpty($all);
//
//        $round->process('mockHand E 1s; discard E 1s');
//        $all = $provider->getAllExecutableList();
//        $this->assertNotEmpty($all);
    }

    /**
     * @dataProvider executableListProvider
     * @param string[] $expected
     * @param bool $contains
     * @param string $actor
     * @param string $script
     */
    function testExecutableList(array $expected, bool $contains, string $actor, string $script) {
        $round = $this->getInitRound();
        $round->process($script);
        $this->assertExecutableList($expected, $contains, $actor);
    }

    function executableListProvider() {
        return [
            // Discard
            [
                ['discard E 1m', 'discard E 5m', 'discard E 0m', 'discard E 9m', 'discard E 6p'],
                true, 'E', 'mockHand E 111155509999m66p',
            ],
            // Discard, swap calling
            [
                ['discard S 2s', 'discard S 5s'],
                false, 'S', 'mockHand E 2s; discard E 2s; mockHand S 234506s; chow S 34s',
            ],

            // Riichi slow+0.6s todo
//            [
//                ['riichi E 1s', 'riichi E 2s', 'riichi E 4s', 'riichi E 5s'],
//                true, 'E', 'mockHand E 123456789m12345s',
//            ],
            // NineNineDraw
            [
                ['nineNineDraw E'],
                true, 'E', 'mockHand E 129m4444pESWNCPF',
            ],
            [
                ['nineNineDraw E'],
                false, 'E', 'mockHand E 122m4444pESWNCPF',
            ],
            // Tsumo
            [
                ['tsumo E'],
                true, 'E', 'mockHand E 123456789m12344s',
            ],
            [
                ['tsumo E'],
                true, 'E', 'mockHand E 119m19p19sESWNCPF',
            ],
            [
                ['tsumo E'],
                false, 'E', 'mockHand E 123456789m12345s',
            ],
            // ConcealedKong
            [
                ['concealedKong E 1111m', 'concealedKong E 5550m'],
                true, 'E', 'mockHand E 111155509m12345s'
            ],
            // ExtendKong
            [
                ['extendKong S 0m 550m'],
                true, 'S', 'mockHand E 5m; discard E 5m; mockHand S 500m23456789p13s; mockNextReplace 1p; pung S 0m5m'
            ],
            // Ron
            [
                ['ron S'],
                true, 'S', 'mockHand E 5m; discard E 5m; mockHand S 34m123456789p11s'
            ],
            // chow
            [
                ['chow S 35m', 'chow S 30m'],
                true, 'S', 'mockHand E 4m; discard E 4m; mockHand S 3509m123456789p'
            ],
            [
                ['chow S 23m', 'chow S 35m', 'chow S 30m', 'chow S 56m', 'chow S 06m'],
                true, 'S', 'mockHand E 4m; discard E 4m; mockHand S 23506m12345678p'
            ],
            [
                ['chow S 34m'],
                false, 'S', 'mockHand E 4s; discard E 4s; mockHand S 3459m123456789p'
            ],
            // pung
            [
                ['pung S 55m', 'pung S 50m'],
                true, 'S', 'mockHand E 5m; discard E 5m; mockHand S 5509m123456789p'
            ],
            [
                ['pung S 55m'],
                false, 'S', 'mockHand E 4m; discard E 4m; mockHand S 5559m123456789p'
            ],
            // kong
            [
                ['kong S 550m'],
                true, 'S', 'mockHand E 5m; discard E 5m; mockHand S 5509m123456789p'
            ],
            [
                ['kong S 555m'],
                false, 'S', 'mockHand E 4m; discard E 4m; mockHand S 5555m123456789p'
            ],
        ];
    }
}
