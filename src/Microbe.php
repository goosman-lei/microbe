<?php
namespace Microbe;
class Microbe {
    public $config;

    protected $milestones = [];

    public static $ins;

    protected function __construct() {
    }

    public static function init(\Microbe\Config $config) {
        if (isset(self::$ins)) {
            return ;
        }
        self::$ins         = new self();
        self::$ins->config = config;
    }

    public function milestoneDefine($name, \Microbe\Chain $chain) {
        $this->milestones[$name] = $chain;
        $this->appendChain($chain);
    }

    public function milestoneInstallChain($name) {
        if (!isset($this->milestones[$name])) {
            throw new RuntimeException("Milestone [$name] not defined");
        }
        /* 系统层级的责任链安装 */
        $argv   = func_get_args();
        $systemChains = array_slice($argv, 1);
        foreach ($systemChains as $chain) {
            $this->milestoneAppendChain($name, $chain);
        }

        /* 用户自定义的责任链安装 */
        $chains = $this->config->get('milestone.' . $name);
        if (empty($chains)) {
            return;
        }
        foreach ($chains as $chain) {
            $class  = $chain['class'];
            $config = $chain['class'];
            $this->milestoneAppendChain($name, new $class($config['config']));
        }
    }

    protected function milestoneAppendChain($name, \Microbe\Chain $chain) {
        $milestoneChain = $this->milestones[$name];
        $milestoneChain->prev->next = $chain;
        $chain->prev = $milestoneChain->prev;
        $chain->next = $milestoneChain;
        $milestoneChain->prev = $chain;
    }

    protected function appendChain(\Microbe\Chain $chain) {
        if (isset($this->chainTail)) {
            $chain->next = null;
            $chain->prev = $this->chainTail;
            $this->chainTail->next = $chain;
            $this->chainTail       = $chain;
        } else {
            $chain->prev = null;
            $chain->next = null;
            $this->chainTail = $this->chainHead = $chain;
        }
    }

}
