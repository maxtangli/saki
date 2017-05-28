<?php

namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Saki\Util\Singleton;

/**
 * @package Nodoka\Server
 */
class NullClient extends Singleton implements ConnectionInterface {
    function send($data) {
        // do nothing
    }

    function close() {
        // do nothing
    }
}