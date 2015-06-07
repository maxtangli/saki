<?php
namespace Saki;

use Saki\Util\ArrayLikeObject;

class TileList extends ArrayLikeObject {
    const REGEX_EMPTY_LIST = '()';
    const REGEX_SUIT_TOKEN = '('.Tile::REGEX_SUIT_NUMBER.'+'.TileType::REGEX_SUIT_TYPE.')';
    const REGEX_HONOR_TOKEN = Tile::REGEX_HONOR_TILE;
    const REGEX_NOT_EMPTY_LIST = '('.self::REGEX_SUIT_TOKEN.'|'.self::REGEX_HONOR_TOKEN.')+';
    const REGEX_LIST = '('.self::REGEX_EMPTY_LIST.'|'.self::REGEX_NOT_EMPTY_LIST.')';

    static function validString($s) {
        $regex = '/^'.self::REGEX_LIST.'$/';
        return preg_match($regex, $s) === 1;
    }

    /**
     * @param string $s
     * @param bool $readonly
     * @return TileList
     */
    static function fromString($s, $readonly = false) {
        if (!static::validString($s)) {
            throw new \InvalidArgumentException("Invalid \$s[$s].");
        }

        $tiles = [];
        $tileType = null;
        for ($i = strlen($s) - 1; $i >= 0; --$i) {
            $c = $s[$i];
            if (is_numeric($c)) {
                array_unshift($tiles, new Tile($tileType, intval($c)));
            } else {
                $tileType = TileType::fromString($c);
                if ($tileType->isHonor()) {
                    array_unshift($tiles, new Tile($tileType));
                }
            }
        }

        return new static($tiles, $readonly);
    }

    private $readonly;
    function __construct(array $tiles, $readonly = false) {
        parent::__construct($tiles);
        $this->readonly = $readonly;
    }

    function getReadonly() {
        return $this->readonly;
    }

    protected function assertWritable() {
        if ($this->getReadonly()) {
            throw new \BadMethodCallException('Invalid method call on a readonly TileList.');
        }
    }

    function __toString() {
        // 123m456p789s東東東中中
        $s = "";
        $tiles = $this->toArray();
        $len = count($tiles);
        for ($i = 0; $i < $len; ++$i) {
            $tile = $tiles[$i];
            if ($tile->getTileType()->isSuit()) {
                $doNotPrintSuit = isset($tiles[$i + 1]) && $tiles[$i + 1]->getTileType()->isSuit() && $tiles[$i + 1]->getTileType() == $tile->getTileType();
                $s .= $doNotPrintSuit ? $tile->getNumber() : $tile;
            } else {
                $s .= $tile;
            }
        }
        return $s;
    }

    /**
     * @param Tile $tileOnHand
     * @return int first index of $tileOnHand
     */
    function toTargetIndex(Tile $tileOnHand) {
        $i = array_search($tileOnHand, $this->toArray());
        if ($i === false) {
            throw new \InvalidArgumentException("Invalid target \$tile[$tileOnHand] for TileList\$this[$this].");
        }
        return $i;
    }

    function add(Tile $newTile) {
        $this->addMany([$newTile]);
    }

    function addMany(array $tiles) {
        $this->assertWritable();
        $newTiles = $this->toArray();
        $newTiles = array_merge($newTiles, $tiles);
        $this->setInnerArray($newTiles);
    }

    function replace(Tile $onHandTile, Tile $newTile) {
        $this->assertWritable();
        $targetIndex = $this->toTargetIndex($onHandTile);
        $newTiles = $this->toArray();
        $newTiles[$targetIndex] = $newTile;
        $this->setInnerArray($newTiles);
    }

    function remove(Tile $onHandTile) {
        $this->assertWritable();
        $newTiles = $this->toArray();
        $targetIndex = $this->toTargetIndex($onHandTile);
        array_splice($newTiles, $targetIndex, 1);
        $this->setInnerArray($newTiles);
    }

    function removeMany(array $onHandTiles) {
        $this->assertWritable();
        foreach ($onHandTiles as $onHandTile) {
            $this->remove($onHandTile);
        }
    }

    /**
     * @return Tile[]
     */
    public function toArray() {
        return parent::toArray();
    }

    /**
     * @param int $offset
     * @return Tile
     */
    function offsetGet($offset) {
        return parent::offsetGet($offset);
    }
}

