<?php

use Nodoka\server\LobbyServer;
use Nodoka\Server\MockClient;
use Ratchet\ConnectionInterface;

class LobbyServerTest extends \SakiTestCase {
    /** @var LobbyServer */
    private $lobbyServer;
    /** @var ConnectionInterface */
    private $client1;

    protected function setUp() {
        parent::setUp();
        $this->lobbyServer = new LobbyServer(true);
        $this->client1 = new MockClient();
        $this->lobbyServer->onOpen($this->client1);
    }

    function testAuth() {
        $server = $this->lobbyServer;
        $client = $this->client1;
        $server->onMessage($client, 'auth Koromo');
        $this->assertEquals('Koromo', $server->getUser($client)->username);
    }

    function testTable() {
        $server = $this->lobbyServer;
        $client = $this->client1;
        $tableId = 0;

        $server->onMessage($client, "tableJoin $tableId");
        $table = $server->getTableById($tableId);
        $this->assertEquals(1, $table->getUserCount());

        $this->assertEquals(0, $table->getReadyCount());
        $server->onMessage($client, "tableReady");
        $this->assertEquals(1, $table->getReadyCount());

        $server->onMessage($client, "tableUnready");
        $this->assertEquals(0, $table->getReadyCount());

        $server->onMessage($client, 'tableLeave');
        $this->assertEquals(0, $table->getUserCount());
    }
}