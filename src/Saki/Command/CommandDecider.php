<?php
namespace Saki\Command;

/**
 * @package Saki\Command
 */
interface CommandDecider {
    function clear();

    /**
     * @return bool
     */
    function decided();

    /**
     * @return PublicCommand
     */
    function getDecided();

    /**
     * @param PublicCommand $publicCommand
     * @return bool
     */
    function isDecidedCommand(PublicCommand $publicCommand);

    /**
     * @param PublicCommand $publicCommand
     * @return bool
     */
    function allowSubmit(PublicCommand $publicCommand);

    /**
     * @param PublicCommand $publicCommand
     */
    function submit(PublicCommand $publicCommand);
}