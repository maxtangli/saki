<?php

namespace Nodoka\Server;

use MongoDB;

/**
 * @package Nodoka\Server
 */
class Persistence {
    private $collection;

    function __construct() {
        $client = new MongoDB\Client("mongodb://localhost:27017");
        $db = $client->selectDatabase('Nodoka');
        $collection = $db->selectCollection('user');

        $this->collection = $collection;
    }

    /**
     * @param string $username
     * @return User
     */
    function load(string $username) {
        $result = $this->collection->findOne(['username' => $username]);
        if (!isset($result)) {
            throw new \InvalidArgumentException("\$username[$username] not existed.");
        }

        return new User(new NullClient());
    }

    /**
     * @param User $user
     */
    function save(User $user) {

    }
}