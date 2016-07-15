<?php
namespace Saki\Command\ParamDeclaration;

/**
 * @package Saki\Command\ParamDeclaration
 */

abstract class ParamDeclaration {
    /**
     * @param ParamDeclaration[] $paramDeclarations
     * @param string[] $paramStrings
     * @return array
     */
    static function toObjects(array $paramDeclarations, array $paramStrings) {
        $valid = count($paramDeclarations) == count($paramStrings);
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf(implode($paramStrings).',',get_called_class())
            );
        }

        // ['E','1m'] => [SeatWind, Tile]
        $objects = [];
        foreach ($paramDeclarations as $i => $paramDeclaration) {
            $paramString = $paramStrings[$i];
            /** @var ParamDeclaration $param */
            $param = new $paramDeclaration($paramString);
            $objects[] = $param->toObject();
        }
        return $objects;
    }

    private $paramString;

    /**
     * @param string $paramString
     */
    function __construct(string $paramString) {
        $this->paramString = $paramString;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getParamString();
    }

    /**
     * @return string
     */
    function getParamString() {
        return $this->paramString;
    }

    //region subclass hooks
    /**
     * @return mixed
     */
    abstract function toObject();
    //endregion
}