<?php

namespace Nodoka\Server;

/**
 * @package Nodoka\Server
 */
interface Authenticator {
    function authenticate(string $username, string $password);
}
