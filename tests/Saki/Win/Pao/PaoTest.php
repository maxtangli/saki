<?php

class PaoTest extends \SakiTestCase {
    function testBigThreeDragonsPao() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E CCCCPPPP; concealedKong E CCCC; concealedKong E PPPP',
            'skipTo N true; mockHand N F; discard N F; mockHand E FF; pung E FF',
            'skip 4; mockHand E 12344s; tsumo E'
        );
        $this->assertPoints([
            25000 + 48000,
            25000,
            25000,
            25000 - 48000
        ]);
    }

    function testBigFourWindsPao() {
        $round = $this->getInitRound();
        $round->process(
            'mockHand E EEEESSSSWWWW; concealedKong E EEEE; concealedKong E SSSS; concealedKong E WWWW',
            'skipTo N true; mockHand N N; discard N N; mockHand E NN; pung E NN',
            'skip 4; mockHand E 11s; tsumo E'
        );
        $this->assertPoints([
            25000 + 48000,
            25000,
            25000,
            25000 - 48000
        ]);
    }
}