<?php

use Saki\Play\MockUser;
use Saki\Play\Room;
use Saki\Play\Roomer;
use Saki\Play\RoomState;

class RoomTest extends \SakiTestCase {
    function testState() {
        $room = new Room();
        $user = new MockUser();
        $roomer = $room->getRoomerOrGenerate($user);
        $this->assertState($roomer, RoomState::NULL);

        $roomer->join();
        $this->assertState($roomer, RoomState::UNAUTHORIZED);
        $this->assertResponseOk($roomer, true);

        $roomer->authorize();
        $this->assertState($roomer, RoomState::IDLE);
        $this->assertResponseOk($roomer, true);

        $roomer->matchingOn();
        $this->assertState($roomer, RoomState::MATCHING);
        $this->assertResponseOk($roomer, true);

        $roomer->matchingOff();
        $this->assertState($roomer, RoomState::IDLE);
        $this->assertResponseOk($roomer, true);

        $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $roomer->matchingOn();
        $this->assertState($roomer, RoomState::PLAYING);
        $this->assertResponseOk($roomer, true);

        $roomer->play('mockHand E E');
        $this->assertResponseRound($roomer, true);
    }

    /**
     * @param Roomer $roomer
     * @param int $value
     */
    static function assertState(Roomer $roomer, int $value) {
        static::assertEquals(RoomState::create($value), $roomer->getRoomState());
    }

    /**
     * @param Roomer $roomer
     * @param bool $clear
     */
    static function assertResponseOk(Roomer $roomer, bool $clear) {
        static::assertResponse($roomer, 'isOk', $clear);
    }

    /**
     * @param Roomer $roomer
     * @param bool $clear
     */
    static function assertResponseRound(Roomer $roomer, bool $clear) {
        static::assertResponse($roomer, 'isRound', $clear);
    }

    /**
     * @param Roomer $roomer
     * @param string $predicate
     * @param bool $clear
     */
    static function assertResponse(Roomer $roomer, string $predicate, bool $clear) {
        /** @var MockUser $mockUser */
        $mockUser = $roomer->getUserProxy();
        $response = $mockUser->getLastResponse();
        $result = call_user_func([$response, $predicate]);
        static::assertTrue($result, "$response");

        if ($clear) {
            $mockUser->clearResponseList();
        }
    }
}