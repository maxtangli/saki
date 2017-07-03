<?php

use Nodoka\Server\LobbyServer;
use Nodoka\Server\MockClient;
use Nodoka\Server\User;
use Saki\Game\PlayerType;
use Saki\Play\Participant;
use Saki\Play\Play;

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

    /**
     * @param Play $play
     * @param bool $asMap
     * @return array list($clientE, $clientS, $clientW, $clientN)
     */
    function getESWNClients(Play $play, bool $asMap = false) {
        $toConnection = function (Participant $participant) {
            /** @var User $user */
            $user = $participant->getUserKey();
            return $user->getConnection();
        };
        $connections = $play->getParticipantList()->toArray($toConnection);

        return $asMap
            ? PlayerType::create(4)->getSeatWindMapping($connections)
            : $connections;
    }

    function testAuth() {
        $server = $this->lobbyServer;
        $client = $this->client1;
        $server->onClose($client);
        $server->onOpen($client);
        $server->onMessage($client, 'auth Koromo pw');
        $this->assertEquals('Koromo', $server->getUser($client)->getUsername());
        $this->assertResponseOk([$client]);
    }

    function testMessageError() {
        $server = new LobbyServer();
        $client = new MockClient();
        $server->onOpen($client);
        try {
            ob_start();
            $server->onMessage($client, 'auth Koromo');
            ob_end_clean();
        } catch (\Throwable $e) {
            ob_end_flush();
            $this->fail('Failed to assert no throw when debugError=false.');
        }

        $this->assertResponseError([$client]);
    }

    function testMatching() {
        $server = $this->lobbyServer;
        $clients = $this->clients;
        foreach ($clients as $client) {
            $server->onMessage($client, 'join');
        }
        $this->assertResponseOk($clients, 0);
        $this->assertResponseRound($clients, 'E', null, 1);
    }

    function testPlay() {
        $server = $this->lobbyServer;
        $clients = $this->clients;
        foreach ($clients as $client) {
            $server->onMessage($client, 'join');
        }

        $play = $server->getRoom()->getPlay($server->getUser($clients[1]));
        $play->getRound()->getDebugConfig()->enableDecider(false);
        $participantE = $play->getCurrentParticipant();
        /** @var User $userE */
        $userE = $participantE->getUserKey();
        $clientE = $userE->getConnection();
        $server->onMessage($clientE, 'play mockHand E E');
        $server->onMessage($clientE, 'play discard E E');
        $server->onMessage($clientE, 'play passAll');
        $this->assertResponseRound($clients, 'S');
    }

    function testRole() {
        // first round
        $server = $this->lobbyServer;
        $clients = $this->clients;
        foreach ($clients as $client) {
            $server->onMessage($client, 'join');
        }

        $play = $server->getRoom()->getPlay($server->getUser($clients[1]));
        foreach ($this->getESWNClients($play, true) as $actor => $connection) {
            $this->assertResponseRound([$connection], null, $actor);
        }

        // second round
        $server->onMessage($this->client1, 'play skipToLast');
        $server->onMessage($this->client1, 'play skip 1');
        $server->onMessage($this->client1, 'play toNextRound');
        foreach ($this->getESWNClients($play, true) as $actor => $connection) {
            $this->assertResponseRound([$connection], null, $actor);
        }
    }

    function testLostConnection() {
        $server = $this->lobbyServer;
        $clients = $this->clients;
        foreach ($clients as $client) {
            $server->onMessage($client, 'join');
        }

        $client1 = $clients[1];
        $server->onClose($client1);

        $client1Return = new MockClient();
        $server->onOpen($client1Return);
        $server->onMessage($client1Return, 'auth client1 pw');
        $this->assertResponseOk([$client1Return], 0);
        $this->assertResponseRound([$client1Return], null, null, 1);
        $otherClients = array_slice($clients, 1);
        $this->assertResponseRound($otherClients);
    }

    function testAI() {
        $server = $this->lobbyServer;
        $clients = $this->clients;
        foreach ($clients as $client) {
            $server->onMessage($client, 'join');
        }

        $play = $server->getRoom()->getPlay($server->getUser($clients[1]));
        $play->getRound()->getDebugConfig()->enableDecider(false);
        list($clientE, $clientS, $clientW, $clientN) = $this->getESWNClients($play);

        // ai triggered by lost connection
        // private phase ai
        $server->onClose($clientE);
        $this->assertResponseRound([$clientS, $clientW, $clientN]);

        // public phase ai pass
        $server->onClose($clientW);
        $server->onClose($clientN);
        $server->onMessage($clientS, 'play pass S');
        $this->assertResponseRound([$clientS], 'S');

        // solo play
        $server->onMessage($clientS, 'play mockHand S S');
        $server->onMessage($clientS, 'play discard S S');
        $this->assertResponseRound([$clientS], 'W');

        $server->onMessage($clientS, 'play pass S');
        $this->assertResponseRound([$clientS], 'N');

        $server->onMessage($clientS, 'play pass S');
        $this->assertResponseRound([$clientS], 'E');

        $server->onMessage($clientS, 'play pass S');
        $this->assertResponseRound([$clientS], 'S');
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
    static function assertResponseError(array $clients, $index = -1) {
        foreach ($clients as $client) {
            static::assertEquals('error', $client->getReceived($index)->response);
        }
    }

    /**
     * @param MockClient[] $clients
     * @param string $current
     * @param string|null $self
     * @param int $index
     */
    static function assertResponseRound(array $clients, string $current = null, string $self = null, $index = -1) {
        foreach ($clients as $client) {
            $received = $client->getReceived($index);
            static::assertTrue(isset($received->round), implode("\n", $client->getReceivedHistory()));

            if (isset($current)) {
                static::assertEquals($current, $received->round->current);
            }

            if (isset($self)) {
                static::assertEquals($self, $received->relations->self);
            }
        }
    }
}