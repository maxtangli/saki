<?php
namespace Saki;

use Traversable;

class Hand implements \IteratorAggregate{
    /**
     * @var Tile[]
     */
    protected $tiles;

    function __construct(array $tiles) {
        $tmp = $tiles;
        usort($tmp, function (Tile $a, Tile $b) {
            return $a->getDisplayOrder() - $b->getDisplayOrder();
        });
        $this->tiles = array_reverse($tmp);
    }

    function __toString() {
        // 123m456p789s東東東中中
        $s = "";
        $tiles = $this->tiles;
        $len = count($tiles);
        for ($i = 0; $i < $len; ++$i) {
            $tile = $tiles[$i];
            if ($tile->isSuit()) {
                $doNotPrintSuit = isset($tiles[$i+1]) && $tiles[$i+1]->isSuit() && $tiles[$i+1]->getSuit()==$tile->getSuit();
                $s .= $doNotPrintSuit ? $tile->getNumber() : $tile;
            }  else {
                $s .= $tile;
            }
        }
        return $s;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new \ArrayIterator($this->tiles);
    }
}