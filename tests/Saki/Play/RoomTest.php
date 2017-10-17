<?php

use Saki\Play\DisconnectedUser;
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
        $this->assertInRoomCount($room, 0);
        $this->assertMatchingCount($room, 0);

        $roomer->join();
        $this->assertState($roomer, RoomState::UNAUTHORIZED);
        $this->assertResponseOk($roomer, true);
        $this->assertInRoomCount($room, 1);
        $this->assertMatchingCount($room, 0);

        $roomer->authorize();
        $this->assertState($roomer, RoomState::IDLE);
        $this->assertResponseOk($roomer, true);
        $this->assertInRoomCount($room, 1);
        $this->assertMatchingCount($room, 0);

        $roomer->matchingOn();
        $this->assertState($roomer, RoomState::MATCHING);
        $this->assertResponseOk($roomer, true);
        $this->assertInRoomCount($room, 1);
        $this->assertMatchingCount($room, 1);

        $roomer->matchingOff();
        $this->assertState($roomer, RoomState::IDLE);
        $this->assertResponseOk($roomer, true);
        $this->assertInRoomCount($room, 1);
        $this->assertMatchingCount($room, 0);

        $roomer2 = $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $this->assertState([$roomer2], RoomState::MATCHING);
        $this->assertInRoomCount($room, 2);
        $this->assertMatchingCount($room, 1);
        $roomer3 = $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $this->assertState([$roomer2, $roomer3], RoomState::MATCHING);
        $this->assertInRoomCount($room, 3);
        $this->assertMatchingCount($room, 2);
        $roomer4 = $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $this->assertState([$roomer2, $roomer3, $roomer4], RoomState::MATCHING);
        $this->assertInRoomCount($room, 4);
        $this->assertMatchingCount($room, 3);

        $roomer->matchingOn();
        $roomers = [$roomer, $roomer2, $roomer3, $roomer4];
        $this->assertState($roomers, RoomState::PLAYING);
        $this->assertResponseOk($roomers, true);
        $this->assertInRoomCount($room, 4);
        $this->assertMatchingCount($room, 0);

        $roomer->play('mockHand E E');
        $this->assertState($roomers, RoomState::PLAYING);
        $this->assertResponseRound($roomers, true);
        $this->assertInRoomCount($room, 4);
        $this->assertMatchingCount($room, 0);

        $roomer->play('toGameOver');
        $this->assertState($roomers, RoomState::IDLE);
        $this->assertResponseRound($roomers, true);
        $this->assertInRoomCount($room, 4);
        $this->assertMatchingCount($room, 0);
    }

    /**
     * @depends testState
     */
    function testLeave() {
        $room = new Room();
        $user = new MockUser();
        $roomer = $room->getRoomerOrGenerate($user);

        $roomer->join()->leave();
        $this->assertState($roomer, RoomState::NULL);
        $this->assertResponseOk($roomer, true);
        $this->assertInRoomCount($room, 0);

        $roomer->join()->authorize()->leave();
        $this->assertState($roomer, RoomState::NULL);
        $this->assertResponseOk($roomer, true);
        $this->assertInRoomCount($room, 0);

        $roomer->join()->authorize()->matchingOn()->leave();
        $this->assertState($roomer, RoomState::NULL);
        $this->assertResponseOk($roomer, true);
        $this->assertMatchingCount($room, 0);
        $this->assertInRoomCount($room, 0);
    }

    /**
     * @depends testLeave
     */
    function testLeaveWhenPlaying() {
        $room = new Room();

        $roomer = $room->getRoomerOrGenerate(new MockUser('loser'))->join()->authorize()->matchingOn();
        $roomer2 = $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $roomer3 = $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();
        $roomer4 = $room->getRoomerOrGenerate(new MockUser())->join()->authorize()->matchingOn();

        // if disconnected when playing, roomer remains by mock user
        $roomer->leave();
        $this->assertInstanceOf(DisconnectedUser::class, $roomer->getUserProxy());
        $this->assertInRoomCount($room, 4);

        // todo roomer keep playing by ai

        // if reconnected when remain playing by mock user, become roomer again
        $userReconnected = new MockUser('loser');
        $roomerReconnected = $room->getRoomerOrGenerate($userReconnected);
        $this->assertSame($userReconnected, $roomerReconnected->getUserProxy());
        $this->assertInRoomCount($room, 4);

        // when game over, remove all disconnected roomer
        $roomer2->leave();
        $roomer3->leave();
        $roomer4->leave();
        $this->assertState([$roomer2, $roomer3, $roomer4], RoomState::PLAYING);
        $this->assertInRoomCount($room, 4);
        $roomerReconnected->play('toGameOver');
        $this->assertState([$roomer2, $roomer3, $roomer4], RoomState::NULL);
        $this->assertInRoomCount($room, 1);
    }

    /**
     * @param Roomer|array $roomers
     * @param int $value
     */
    static function assertState($roomers, int $value) {
        foreach ($roomers as $roomer) {
            static::assertEquals(RoomState::create($value), $roomer->getRoomState());
        }
    }

    /**
     * @param Roomer|array $roomers
     * @param bool $clear
     */
    static function assertResponseOk($roomers, bool $clear) {
        static::assertResponse($roomers, 'isOk', $clear);
    }

    /**
     * @param Roomer|array $roomers
     * @param bool $clear
     */
    static function assertResponseRound($roomers, bool $clear) {
        static::assertResponse($roomers, 'isRound', $clear);
    }

    /**
     * @param Roomer|array $roomers
     * @param string $predicate
     * @param bool $clear
     */
    static function assertResponse($roomers, string $predicate, bool $clear) {
        $roomers = is_array($roomers) ? $roomers : [$roomers];
        foreach ($roomers as $roomer) {
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