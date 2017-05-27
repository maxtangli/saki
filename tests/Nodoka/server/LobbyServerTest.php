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

    function testTableJoinAndLeave() {
        $server = $this->lobbyServer;
        $client = $this->client1;
        $tableId = 0;
        $server->onMessage($client, "tableJoin $tableId");
        $this->assertEquals(1, $server->getTableById($tableId)->getUserCount());
        $server->onMessage($client, 'tableLeave');
        $this->assertEquals(0, $server->getTableById($tableId)->getUserCount());
    }
}