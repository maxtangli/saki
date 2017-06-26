<?php

use Nodoka\Server\LobbyServer;
use Nodoka\Server\MockClient;

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
    /** @var MockClient[] */
    private $clients;

    protected function setUp() {
        parent::setUp();
        $server = $this->lobbyServer = new LobbyServer(true);
        $client1 = $this->client1 = new MockClient();
        $client2 = $this->client2 = new MockClient();
        $client3 = $this->client3 = new MockClient();
        $client4 = $this->client4 = new MockClient();
        $clients = $this->clients = [1 => $client1, 2 => $client2, 3 => $client3, 4 => $client4];
        /**
         * @var int $i
         * @var MockClient $client
         */
        foreach ($clients as $i => $client) {
            $server->onOpen($client);
            $server->onMessage($client, "auth client{$i} pw");
            $client->clearReceived();
        }
    }

    function testAuth() {
        $server = $this->lobbyServer;
        $client = $this->client1;
        $server->onMessage($client, 'auth Koromo pw');
        $this->assertEquals('Koromo', $server->getUser($client)->getUsername());
        $this->assertResponseOk([$client]);
    }

    function testMatching() {
        $server = $this->lobbyServer;
        $server->onMessage($this->client1, 'join');
        $server->onMessage($this->client2, 'join');
        $server->onMessage($this->client3, 'join');
        $server->onMessage($this->client4, 'join');
        $this->assertResponseOk($this->clients, 0);
        $this->assertResponseRound($this->clients, 1);
    }

    /**
     * @param MockClient[] $clients
     * @param int $index
     */
    static function assertResponseOk(array $clients, $index = -1) {
        foreach ($clients as $client) {
            static::assertEquals('ok', $client->getReceived($index)->response);
        }
    }

    /**
     * @param MockClient[] $clients
     * @param int $index
     */
    static function assertResponseRound(array $clients, $index = -1) {
        foreach ($clients as $client) {
            static::assertTrue(isset($client->getReceived($index)->round), implode("\n", $client->getReceivedHistory()));
        }
    }
}