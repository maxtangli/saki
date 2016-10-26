<?php

use Nodoka\Server\LocalClient;
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
        $client1 = new LocalClient();
        $play->onOpen($client1);
        $this->assertViewer('E', $client1);

        $client2 = new LocalClient();
        $play->onOpen($client2);
        $this->assertViewer('S', $client2);

        $client3 = new LocalClient();
        $play->onOpen($client3);
        $this->assertViewer('W', $client3);

        $client4 = new LocalClient();
        $play->onOpen($client4);
        $this->assertViewer('N', $client4);

        // reallocate in wind order after close
        $play->onClose($client3);
        $play->onClose($client2);

        $client2 = new LocalClient();
        $play->onOpen($client2);
        $this->assertViewer('S', $client2);

        $client3 = new LocalClient();
        $play->onOpen($client3);
        $this->assertViewer('W', $client3);

        // viewer assigned if four player joined
        $client5 = new LocalClient();
        $play->onOpen($client5);
        $this->assertViewer('E', $client5);

        $client6 = new LocalClient();
        $play->onOpen($client6);
        $this->assertViewer('E', $client6);

        // viewer no see commands
        $this->assertCommands(true, $client1);
        $this->assertCommands(false, $client5);
        $this->assertCommands(false, $client6);

        // todo player no see other's public, target
    }

    private function assertViewer(string $expected, LocalClient $client) {
        $json = $client->getLastReceived();
        $jsonSelfActor = $json->areas->self->actor;
        $this->assertEquals($expected, $jsonSelfActor);
    }

    private function assertCommands(bool $exist, LocalClient $client) {
        $json = $client->getLastReceived();
        $jsonCommands = $json->areas->self->commands;
        $this->assertExist($exist, $jsonCommands);
    }
}