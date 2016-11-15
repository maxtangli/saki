<?php
namespace Saki\Play;

/**
 * @package Saki\Play
 */
class Participant {
    private $userKey;
    private $role;
    private $roundSerializer;

    /**
     * @param $userKey
     * @param Role $role
     * @param RoundSerializer $roundSerializer
     */
    function __construct($userKey, Role $role, RoundSerializer $roundSerializer) {
        $this->userKey = $userKey;
        $this->role = $role;
        $this->roundSerializer = $roundSerializer;
    }

    /**
     * @return string
     */
    function __toString() {
        $id = $this->getUserKey()->resourceId; // todo better wrap
        return $id . ',' . $this->getRole();
    }

    /**
     * @return mixed
     */
    function getUserKey() {
        return $this->userKey;
    }

    /**
     * @return Role
     */
    function getRole() {
        return $this->role;
    }

    /**
     * @return RoundSerializer
     */
    function getRoundSerializer() {
        return $this->roundSerializer;
    }
}