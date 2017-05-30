<?php

use Nodoka\server\LobbyServer;
use Nodoka\Server\MockClient;
use Nodoka\server\User;
use Ratchet\ConnectionInterface;
use Saki\Game\SeatWind;
use Saki\Play\Participant;

class LobbyServerTest extends \SakiTestCase {
    /** @var LobbyServer */
    private $lobbyServer;
    /** @var MockClient */
    private $client1;
    /** @var MockClient */
    private $client2;
    /** @var MockClient */
    private $client3;
    /** @var MockClient */
    private $client4;

    protected function setUp() {
        parent::setUp();
        $this->lobbyServer = new LobbyServer(true);
        $this->client1 = new MockClient();
        $this->client2 = new MockClient();
        $this->client3 = new MockClient();
        $this->client4 = new MockClient();
        $this->lobbyServer->onOpen($this->client1);
        $this->lobbyServer->onOpen($this->client2);
        $this->lobbyServer->onOpen($this->client3);
        $this->lobbyServer->onOpen($this->client4);
        $this->lobbyServer->onMessage($this->client1, 'auth client1');
        $this->lobbyServer->onMessage($this->client2, 'auth client2');
        $this->lobbyServer->onMessage($this->client3, 'auth client3');
        $this->lobbyServer->onMessage($this->client4, 'auth client4');
    }

    /**
     * @return \Nodoka\server\Table
     */
    private function joinAndReady() {
        $server = $this->lobbyServer;
        $tableId = 0;
        $table = $server->getTableList()->getTableById($tableId);

        $server->onMessage($this->client1, "tableJoin $tableId");
        $server->onMessage($this->client2, "tableJoin $tableId");
        $server->onMessage($this->client3, "tableJoin $tableId");
        $server->onMessage($this->client4, "tableJoin $tableId");
        $server->onMessage($this->client1, "tableReady");
        $server->onMessage($this->client2, "tableReady");
        $server->onMessage($this->client3, "tableReady");
        $server->onMessage($this->client4, "tableReady");

        return $table;
    }

    function testAuth() {
        $server = $this->lobbyServer;
        $client = $this->client1;
        $server->onMessage($client, 'auth Koromo');
        $this->assertEquals('Koromo', $server->getAuthorizedUser($client)->getUsername());
    }

    function testTablePrepare() {
        $server = $this->lobbyServer;
        $client = $this->client1;
        $tableId = 0;
        $table = $server->getTableList()->getTableById($tableId);

        $server->onMessage($client, "tableJoin $tableId");
        $this->assertEquals(1, $table->getUserCount());

        $this->assertEquals(0, $table->getReadyCount());
        $server->onMessage($client, 'tableReady');
        $this->assertEquals(1, $table->getReadyCount());

        $server->onMessage($client, 'tableInfoList');
        $this->assertTrue($client->getLastReceived()[$tableId]->tableUserList[0]->ready);

        $server->onMessage($client, 'tableUnready');
        $this->assertEquals(0, $table->getReadyCount());

        $server->onMessage($client, 'tableLeave');
        $this->assertEquals(0, $table->getUserCount());
    }

    /**
     * @depends testTablePrepare
     */
    function testTableStart() {
        $server = $this->lobbyServer;
        $table = $this->joinAndReady();
        $this->assertTrue($table->isStarted());

        /** @var Participant $participant */
        $participant = $table->getPlay()->getParticipantList(SeatWind::createEast())->getSingle();
        /** @var User $user */
        $user = $participant->getUserKey();
        $client = $user->getConn();

        $tile1 = $table->getPlay()->getRound()
            ->getArea(SeatWind::createEast())->getHand()
            ->getPrivate()->getFirst();
        $server->onMessage($client, "tablePlay discard E $tile1");
        $this->assertTrue($table->getPlay()->getRound()->getPhase()->isPublic());

        $table->finish();
        $this->assertFalse($table->isStarted());
        $this->assertFalse($table->isFullReady());
    }

    /**
     * @depends testTableStart
     */
    function testReconnect() {
        $server = $this->lobbyServer;
        $table = $this->joinAndReady();
        $client1 = $this->client1;
        $user1 = $server->getAuthorizedUser($client1);

        $this->assertEquals(4, $table->getUserCount());

        $server->onClose($client1);
        $this->assertEquals(4, $table->getUserCount());
        $this->assertFalse($user1->isConnected());
        $this->assertTrue($server->getTableList()->inTable($user1->getId()));

        $client1Reconnect = new MockClient();
        $server->onOpen($client1Reconnect);
        $server->onMessage($client1Reconnect, "auth {$user1->getUsername()}");
        $user1Reconnect = $server->getAuthorizedUser($client1Reconnect);
        $this->assertSame($user1, $user1Reconnect);
        $this->assertTrue($user1->isConnected());
    }

//    /**
//     * @depends testTableStart
//     */
//    function testKickLostConnections() {
//
//    }
}