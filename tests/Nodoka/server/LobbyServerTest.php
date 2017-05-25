<?php

use Nodoka\server\LobbyServer;
use Nodoka\Server\MockClient;

class LobbyServerTest extends \SakiTestCase {
    /**
     * @var LobbyServer
     */
    private $lobbyServer;

    protected function setUp() {
        parent::setUp();
        $this->lobbyServer = new LobbyServer();
    }

    function testAuth() {
        $server = $this->lobbyServer;

        $client = new MockClient();
        $server->onOpen($client);

        $server->onMessage($client, 'auth Koromo');
        $this->assertEquals('Koromo', $server->getUser($client)->username);
    }
}