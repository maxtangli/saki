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
}