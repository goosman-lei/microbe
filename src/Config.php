<?php
namespace Microbe;
interface Config {
    public function get($key, $default);
    public function set($key, $value);
}
