<?php

use Nodoka\Server\MockClient;
use Nodoka\Server\PlayServer;
use Saki\Game\SeatWind;

class PlayServerTest extends \SakiTestCase {
    /**
     * @var PlayServer
     */
    private $playServer;

    protected function setUp() {
        parent::setUp();
        $this->playServer = new PlayServer();
        $this->playServer->setLogEnable(false);
    }

    function testDiscard() {
        $playServer = $this->playServer;

        $client1 = new MockClient();
        $playServer->onOpen($client1);

        $tile = $playServer->getPlay()->getRound()->getArea(SeatWind::createEast())
            ->getHand()->getPublic()->getFirst();
        $command = "discard E $tile";
        $playServer->onMessage($client1, $command);
        $this->assertTrue(true); // todo
    }

    function testViewerAssign() {
        $playServer = $this->playServer;

        // initial
        $client1 = new MockClient();
        $playServer->onOpen($client1);
        $this->assertViewer('E', $client1);

        $client2 = new MockClient();
        $playServer->onOpen($client2);
        $this->assertViewer('S', $client2);

        $client3 = new MockClient();
        $playServer->onOpen($client3);
        $this->assertViewer('W', $client3);

        $client4 = new MockClient();
        $playServer->onOpen($client4);
        $this->assertViewer('N', $client4);

        // reallocate in wind order after close
        $playServer->onClose($client3);
        $playServer->onClose($client2);

        $client2 = new MockClient();
        $playServer->onOpen($client2);
        $this->assertViewer('S', $client2);

        $client3 = new MockClient();
        $playServer->onOpen($client3);
        $this->assertViewer('W', $client3);

        // viewer assigned if four player joined
        $client5 = new MockClient();
        $playServer->onOpen($client5);
        $this->assertViewer('E', $client5);

        $client6 = new MockClient();
        $playServer->onOpen($client6);
        $this->assertViewer('E', $client6);

        // viewer no see commands
        $this->assertCommands(true, $client1);
        $this->assertCommands(false, $client5);
        $this->assertCommands(false, $client6);

        // todo player no see other's public, target
    }

    private function assertViewer(string $expected, MockClient $client) {
        // todo

        $json = $client->getLastReceived();
        $jsonSelfActor = $json->relations->self;
        $this->assertEquals($expected, $jsonSelfActor);

        $jsonSelfAreaActor = $json->areas->$jsonSelfActor->actor;
        $this->assertEquals($expected, $jsonSelfAreaActor);
    }

    private function assertCommands(bool $exist, MockClient $client) {
        $json = $client->getLastReceived();
        $actor = $json->relations->self;
        $jsonCommands = $json->areas->$actor->commands;
        $this->assertExist($exist, $jsonCommands);
    }
}