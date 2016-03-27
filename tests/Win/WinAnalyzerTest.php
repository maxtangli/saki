<?php

use Saki\Game\Round;
use Saki\Win\WinTarget;

class WinAnalyzerTest extends \PHPUnit_Framework_TestCase {
    function testPublicPhaseTarget() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard E E:s-5s:5s; mockHand E 123m456m789m123s5s'); // mock 13 tiles

        $target = new WinTarget($r->getTurnManager()->getCurrentPlayer(), $r);

        $dataProvider = [
            ['123456789m12355s', $target->getPrivateHand()->__toString()],
            ['123456789m1235s', $target->getPublicHand()->__toString()],
        ];
        foreach ($dataProvider as list($expected, $actual)) {
            $this->assertEquals($expected, $actual, sprintf('expected[%s] but actual[%s]', $expected, $actual));
        }
    }
}
