<?php

namespace Nodoka\Server;

/**
 * @package Nodoka\Server
 */
class NullAuthenticator implements Authenticator {
    function authenticate(string $username, string $password) {
        return true;
    }
}
