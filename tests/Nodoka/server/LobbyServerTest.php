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
    /** @var ConnectionInterface */
    private $client1;
    /** @var ConnectionInterface */
    private $client2;
    /** @var ConnectionInterface */
    private $client3;
    /** @var ConnectionInterface */
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
    }

    function testAuth() {
        $server = $this->lobbyServer;
        $client = $this->client1;
        $server->onMessage($client, 'auth Koromo');
        $this->assertEquals('Koromo', $server->getUser($client)->username);
    }

    function testTablePrepare() {
        $server = $this->lobbyServer;
        $client = $this->client1;
        $tableId = 0;
        $table = $server->getTableList()->getTableById($tableId);

        $server->onMessage($client, "tableJoin $tableId");
        $this->assertEquals(1, $table->getUserCount());

        $this->assertEquals(0, $table->getReadyCount());
        $server->onMessage($client, "tableReady");
        $this->assertEquals(1, $table->getReadyCount());

        $server->onMessage($client, "tableUnready");
        $this->assertEquals(0, $table->getReadyCount());

        $server->onMessage($client, 'tableLeave');
        $this->assertEquals(0, $table->getUserCount());
    }

    /**
     * @depends testTablePrepare
     */
    function testTableStart() {
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
        $this->assertTrue($table->isStarted());

        /** @var Participant $participant */
        $participant = $table->getPlay()->getParticipantList(SeatWind::createEast())->getSingle();
        /** @var User $client */
        $user = $participant->getUserKey();
        $client = $user->conn;

        $tile1 = $table->getPlay()->getRound()
            ->getArea(SeatWind::createEast())->getHand()
            ->getPrivate()->getFirst();
        $server->onMessage($client, "tablePlay discard E $tile1");
        $this->assertTrue($table->getPlay()->getRound()->getPhase()->isPublic());

        $table->finish();
        $this->assertFalse($table->isStarted());
        $this->assertFalse($table->isFullReady());
    }
}