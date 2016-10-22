<?php

use Nodoka\Server\NullClient;
use Nodoka\Server\Play;

class PlayTest extends \SakiTestCase {
    /**
     * @var Play
     */
    private $play;

    protected function setUp() {
        parent::setUp();
        $play = new Play();
        $play->logOff();
        $this->play = $play;
    }

    function testViewerAssign() {
        $play = $this->play;

        // initial
        $client1 = new NullClient();
        $play->onOpen($client1);
        $this->assertViewer('E', $client1);

        $client2 = new NullClient();
        $play->onOpen($client2);
        $this->assertViewer('S', $client2);

        $client3 = new NullClient();
        $play->onOpen($client3);
        $this->assertViewer('W', $client3);

        $client4 = new NullClient();
        $play->onOpen($client4);
        $this->assertViewer('N', $client4);

        // reallocate in wind order after close
        $play->onClose($client3);
        $play->onClose($client2);

        $client2 = new NullClient();
        $play->onOpen($client2);
        $this->assertViewer('S', $client2);

        $client3 = new NullClient();
        $play->onOpen($client3);
        $this->assertViewer('W', $client3);
    }

    private function assertViewer(string $expected, NullClient $client) {
        $actual = $this->play->getViewer($client);
        $this->assertSeatWind($expected, $actual);

        $json = $client->getLastReceived();
        $jsonSelfActor = $json->areas->self->actor;
        $this->assertEquals($expected, $jsonSelfActor);
    }
}