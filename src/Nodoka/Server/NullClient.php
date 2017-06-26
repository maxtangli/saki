<?php

namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Saki\Util\Singleton;

/**
 * @package Nodoka\Server
 */
class NullClient extends Singleton implements ConnectionInterface {
    //region ConnectionInterface impl
    function send($data) {
        // do nothing
    }

    function close() {
        // do nothing
    }
    //endregion
}