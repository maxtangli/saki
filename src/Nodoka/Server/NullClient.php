<?php

namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Saki\Util\Utils;

/**
 * @package Nodoka\Server
 */
class NullClient implements ConnectionInterface {
    public $resourceId;

    function __construct() {
        $this->resourceId = Utils::generateRandomToken('Null');
    }

    //region ConnectionInterface impl

    function send($data) {
        // do nothing
    }

    function close() {
        // do nothing
    }
    //endregion
}