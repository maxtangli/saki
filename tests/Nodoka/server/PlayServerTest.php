<?php

use Nodoka\Server\MockClient;
use Nodoka\Server\PlayServer;

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

    function testViewerAssign() {
        $play = $this->playServer;

        // initial
        $client1 = new MockClient();
        $play->onOpen($client1);
        $this->assertViewer('E', $client1);

        $client2 = new MockClient();
        $play->onOpen($client2);
        $this->assertViewer('S', $client2);

        $client3 = new MockClient();
        $play->onOpen($client3);
        $this->assertViewer('W', $client3);

        $client4 = new MockClient();
        $play->onOpen($client4);
        $this->assertViewer('N', $client4);

        // reallocate in wind order after close
        $play->onClose($client3);
        $play->onClose($client2);

        $client2 = new MockClient();
        $play->onOpen($client2);
        $this->assertViewer('S', $client2);

        $client3 = new MockClient();
        $play->onOpen($client3);
        $this->assertViewer('W', $client3);

        // viewer assigned if four player joined
        $client5 = new MockClient();
        $play->onOpen($client5);
        $this->assertViewer('E', $client5);

        $client6 = new MockClient();
        $play->onOpen($client6);
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