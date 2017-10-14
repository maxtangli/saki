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
        $this->assertMatchingCount($room, 1);

        $roomer->matchingOff();
        $this->assertState($roomer, RoomState::IDLE);
        $this->assertResponseOk($roomer, true);
        $this->assertMatchingCount($room, 0);

        $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $this->assertMatchingCount($room, 1);
        $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $this->assertMatchingCount($room, 2);
        $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $this->assertMatchingCount($room, 3);
        $roomer->matchingOn();
        $this->assertState($roomer, RoomState::PLAYING);
        $this->assertResponseOk($roomer, true);
        $this->assertMatchingCount($room, 0);

        $roomer->play('mockHand E E');
        $this->assertResponseRound($roomer, true);
    }

    /**
     * @depends testState
     */
    function testLeave() {
        $room = new Room();
        $user = new MockUser();
        $roomer = $room->getRoomerOrGenerate($user);

        $roomer->join();

        $roomer->leave();
        $this->assertState($roomer, RoomState::NULL);
        $this->assertResponseOk($roomer, true);

        $roomer->join();
        $roomer->authorize();
        $roomer->leave();
        $this->assertState($roomer, RoomState::NULL);
        $this->assertResponseOk($roomer, true);

        $roomer->join();
        $roomer->authorize();
        $roomer->matchingOn();
        $roomer->leave();
        $this->assertState($roomer, RoomState::NULL);
        $this->assertResponseOk($roomer, true);
        $this->assertMatchingCount($room,0);


    }

    /**
     * @depends testLeave
     */
    function testLeaveWhenPlaying() {
        $room = new Room();
        $user = new MockUser('loser');
        $roomer = $room->getRoomerOrGenerate($user);

        $roomer->join();
        $roomer->authorize();
        $roomer->matchingOn();
        $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();

        // roomer remains with mock user
        $roomer->leave();
        $this->assertInRoomCount($room, 4);

        // todo roomer keep playing by ai

        // user come back and became roomer again
        $user2 = new MockUser('loser');
        $roomer2 = $room->getRoomerOrGenerate($user2);
        $this->assertSame($user2, $roomer2->getUserProxy());
        $this->assertInRoomCount($room, 4);

        // todo when game over, remove disconnected roomer
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

    /**
     * @param Room $room
     * @param int $n
     */
    static function assertInRoomCount(Room $room, int $n) {
        static::assertEquals($n, $room->getRoomerList()->count());
    }

    /**
     * @param Room $room
     * @param int $n
     */
    static function assertMatchingCount(Room $room, int $n) {
        static::assertEquals($n, $room->getTableMatcher()->getMatchingCount());
    }
}