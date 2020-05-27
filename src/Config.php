<?php
namespace Microbe;
interface Config {
    public function get($key, $default = null);
    public function set($key, $value);
}