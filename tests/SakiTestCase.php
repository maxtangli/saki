<?php

use Saki\Game\Phase;
use Saki\Game\PointItem;
use Saki\Game\PrevailingStatus;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Turn;
use Saki\Tile\Tile;
use Saki\Util\ArrayList;
use Saki\Win\Point\PointList;
use Saki\Win\Result\ResultType;

class SakiTestCase extends \PHPUnit_Framework_TestCase {
    //region PHPUnit_Framework_TestCase override
    static function assertEquals($expected, $actual, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false) {
        if ($message === '' && is_object($expected) && is_object($actual)) {
            $message = sprintf(
                'Failed asserting that two objects are equal, $expected[%s] but $actual[%s].',
                $expected, $actual
            );
        }
        parent::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    static function assertContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false) {
        if ($message === '') {
            $message = sprintf(
                "array[%s]\nneedle[%s]\n",
                implode(' | ', $haystack),
                $needle
            );
        }
        parent::assertContains($needle, $haystack, $message, $ignoreCase, $checkForObjectIdentity, $checkForNonObjectIdentity);
    }
    //endregion

    //region Utils
    /**
     * @param bool $expected
     * @param $actual
     * @param string $message
     */
    static function assertBool(bool $expected, $actual, $message = '') {
        if ($expected) {
            static::assertTrue($actual, $message);
        } else {
            static::assertFalse($actual, $message);
        }
    }
    
    /**
     * @param string $expected
     * @param ArrayList $arrayList
     */
    protected function assertArrayList($expected, ArrayList $arrayList) {
        if (is_string($expected)) {
            $expectedString = $expected;
        } elseif (is_array($expected)) {
            $expectedString = implode(',', $expected);
        } else {
            throw new \InvalidArgumentException();
        }
        $this->assertEquals($expectedString, $arrayList->__toString());
    }
    //endregion

    //region get Round
    private static $round;

    /**
     * @param PrevailingStatus|null $prevailingStatus
     * @return Round
     */
    static function getInitRound(PrevailingStatus $prevailingStatus = null) {
        self::$round = self::$round ?? new Round();
        self::$round->debugInit($prevailingStatus ?? PrevailingStatus::createFirst());
        return self::$round;
    }

    /**
     * @return Round
     */
    static function getCurrentRound() {
        return self::$round ?? self::getInitRound();
    }
    //endregion

    //region Command
    function assertExecutable(string $line) {
        $this->assertExecutableImpl(true, $line);
    }

    function assertNotExecutable(string $line) {
        $this->assertExecutableImpl(false, $line);
    }

    private function assertExecutableImpl(bool $executable, string $line) {
        $round = $this->getCurrentRound();
        $parser = $round->getProcessor()->getParser();
        $command = $parser->parseLine($line);
        
        if ($executable) {
            $message = sprintf('Failed asserting that Command[%s] is executable.', $line);
            $this->assertTrue($command->executable(), $message);
        } else {
            $message = sprintf('Failed asserting that Command[%s] is not executable.', $line);
            $this->assertFalse($command->executable(), $message);
        }
    }
    //endregion

    //region Areas
    function assertHand(string $private = null, string $melded = null, string $targetTile = null,
                        string $seatWind = null) {
        $round = $this->getCurrentRound();
        $actualSeatWind = $seatWind !== null ? SeatWind::fromString($seatWind) : $round->getCurrentSeatWind();
        $hand = $round->getArea($actualSeatWind)->getHand();

        if ($private !== null) { // == vs isLocked
            $this->assertEquals($private, $hand->getPrivate()->__toString());
        }

        if ($melded !== null) { // == vs isLocked
            $this->assertEquals($melded, $hand->getMelded()->__toString());
        }

        if ($targetTile !== null) {
            $this->assertEquals(Tile::fromString($targetTile), $hand->getTarget()->getTile());
        }
    }

    function assertLastOpen(string $tile) {
        $round = $this->getCurrentRound();
        $openHistory = $round->getOpenHistory();
        $this->assertEquals($tile, $openHistory->getLastOpen()->getTile());
    }

    function assertHasClaim(Turn $turn) {
        $round = $this->getCurrentRound();
        $claimHistory = $round->getClaimHistory();
        $this->assertTrue($claimHistory->hasClaim($turn));
    }

    function assertHasNotClaim(Turn $turn) {
        $round = $this->getCurrentRound();
        $claimHistory = $round->getClaimHistory();
        $this->assertFalse($claimHistory->hasClaim($turn));
    }

    function assertCurrentTurnChanged(string $seatWind = null, Turn $laterThanOldTurn = null) {
        $round = $this->getCurrentRound();
        $currentTurn = $round->getTurn();

        if ($seatWind !== null) {
            $this->assertEquals(SeatWind::fromString($seatWind), $currentTurn->getSeatWind());
        }

        if ($laterThanOldTurn !== null) {
            $this->assertTrue($currentTurn->isAfter($laterThanOldTurn));
        }
    }

    function assertCurrentTurnNotChanged(Turn $turn) {
        $round = $this->getCurrentRound();
        $currentTurn = $round->getTurn();
        $this->assertEquals($turn, $currentTurn);
    }
    //endregion

    //region Phase
    function assertPrivate(string $currentSeatWind = null) {
        $this->assertPhaseImpl(Phase::PRIVATE_PHASE, $currentSeatWind);
    }

    function assertPublic(string $currentSeatWind = null) {
        $this->assertPhaseImpl(Phase::PUBLIC_PHASE, $currentSeatWind);
    }

    function assertOver() {
        $this->assertPhaseImpl(Phase::OVER_PHASE);
    }
    
    private function assertPhaseImpl(int $phaseValue, string $expectedCurrentSeatWind = null) {
        $round = $this->getCurrentRound();

        $currentPhase = $round->getPhaseState()->getPhase();
        $this->assertEquals(Phase::create($phaseValue), $currentPhase);

        if ($expectedCurrentSeatWind !== null) {
            $currentSeatWind = $round->getCurrentSeatWind();
            $this->assertEquals(SeatWind::fromString($expectedCurrentSeatWind), $currentSeatWind);
        }
    }

    function assertResultType(int $resultTypeValue) {
        $expected = ResultType::create($resultTypeValue);
        $actual = $this->getCurrentRound()->getPhaseState()->getResult()->getResultType();
        $this->assertEquals($expected, $actual);
    }
    //endregion
}
