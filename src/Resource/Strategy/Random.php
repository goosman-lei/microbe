<?php
namespace Microbe\Resource\Strategy;
class Random {
    public static function select($nodes) {
        return $nodes[array_rand($nodes)];
    }
}
