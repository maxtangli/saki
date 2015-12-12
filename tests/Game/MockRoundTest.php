<?php

use Saki\Game\MockRound;
use Saki\Game\RoundPhase;
use Saki\Tile\Tile;

class MockRoundTest extends PHPUnit_Framework_TestCase {
    function testDebugSkipTo() {
        $r = new MockRound();
        $playerE = $r->getCurrentPlayer();

        // phase not changed
        $targetTile = $r->getRoundData()->getTileAreas()->getTargetTile()->toNextTile();
        $r->debugSkipTo($playerE,null,null,null);
        $this->assertEquals($playerE, $r->getCurrentPlayer());
        $this->assertEquals(RoundPhase::getPrivatePhaseInstance(), $r->getRoundPhase());

        // to public phase

        // to other player

    }
}