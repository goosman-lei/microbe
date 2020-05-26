<?php
namespace Microbe;
abstract class Milestone extends \Microbe\Chain {
    abstract public function exec(\Microbe\Request $request, \Microbe\Response $response);
}
