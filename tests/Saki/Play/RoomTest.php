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

        $roomer->authorize();
        $this->assertState($roomer, RoomState::IDLE);

        $roomer->matchingOn();
        $this->assertState($roomer, RoomState::MATCHING);

        $roomer->matchingOff();
        $this->assertState($roomer, RoomState::IDLE);

        $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $roomer->matchingOn();
        $this->assertState($roomer, RoomState::PLAYING);
    }

    /**
     * @param Roomer $roomer
     * @param int $value
     */
    static function assertState(Roomer $roomer, int $value) {
        static::assertEquals(RoomState::create($value), $roomer->getRoomState());
    }
}