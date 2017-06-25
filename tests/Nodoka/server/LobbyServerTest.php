<?php

use Nodoka\server\LobbyServer;
use Nodoka\Server\MockClient;
use Nodoka\server\User;
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

    function testAuth() {
        $server = $this->lobbyServer;
        $client = $this->client1;
        $server->onMessage($client, 'auth Koromo');
        $this->assertEquals('Koromo', $server->getUser($client)->getUsername());
    }
}