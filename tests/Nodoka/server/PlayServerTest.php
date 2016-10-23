<?php

use Nodoka\Server\NullClient;
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
        $client1 = new NullClient();
        $play->onOpen($client1);
        $this->assertViewer('E', $client1);

        $client2 = new NullClient();
        $play->onOpen($client2);
        $this->assertViewer('E', $client2);

        $client3 = new NullClient();
        $play->onOpen($client3);
        $this->assertViewer('E', $client3);

        $client4 = new NullClient();
        $play->onOpen($client4);
        $this->assertViewer('E', $client4);

        // reallocate in wind order after close
        $play->onClose($client3);
        $play->onClose($client2);

        $client2 = new NullClient();
        $play->onOpen($client2);
        $this->assertViewer('E', $client2);

        $client3 = new NullClient();
        $play->onOpen($client3);
        $this->assertViewer('E', $client3);
    }

    private function assertViewer(string $expected, NullClient $client) {
        $json = $client->getLastReceived();
        $jsonSelfActor = $json->areas->self->actor;
        $this->assertEquals($expected, $jsonSelfActor);
    }
}