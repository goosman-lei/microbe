<?php
namespace Microbe;
abstract class Chain {
    public $prev;
    public $next;

    protected $milestone;

    abstract public function exec(\Microbe\Request $request, \Microbe\Response $response);

    final public function doNext($request, $response) {
        if (isset($this->next)) {
            $this->next->exec($request, $response);
        }
    }

    final public function markMilestone($name) {
        $this->milestone = $name;
        return $this;
    }

    final public function isMilestone() {
        return isset($this->milestone);
    }
}
