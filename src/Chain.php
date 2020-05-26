<?php
namespace Microbe;
abstract class Chain {
    public $prev;
    public $next;

    abstract public function exec(\Microbe\Request $request, \Microbe\Response $response);

    final public function doNext($request, $response) {
        if (isset($this->next)) {
            $this->next->exec($request, $response);
        }
    }
}
