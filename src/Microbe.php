<?php
namespace Microbe;
class Microbe {
    protected $chainHead;
    protected $chainTail;

    protected $chainMapping = [];

    public $config;

    public static $ins;

    public static function init(\Microbe\Config $config) {
        if (isset(self::$ins)) {
            return self::$ins;
        }
        self::$ins = new self();
        self::$ins->config = $config;
        return self::$ins;
    }
    
    public function doChain($request, $response) {
        if (isset($this->chainHead)) {
            $this->chainHead->exec($request, $response);
        }
    }

    public function installUserChain() {
        $userChains = $this->config->get('framework.chain');
        if (empty($userChains)) {
            return;
        }

        foreach ($userChains as $chainName => $chainInfo) {
            if ($chainName[0] == '-') {
                throw new RuntimeException("Invalid user chain name[{$chainName}], it shouldn't begin with '-'");
            }

            $class  = $chainInfo['class'];
            $config = $chainInfo['config'];
            $direction = $chainInfo['direction'] == 'prepend' ? 'prepend' : 'append';
            $target    = empty($chainInfo['target']) ? null : $chainInfo['target'];

            if ($direction == 'append') {
                $this->appendChain(new $class($config), $chainName, $target);
            } else {
                $this->prependChain(new $class($config), $chainName, $target);
            }
        }
    }

    public function appendChain(\Microbe\Chain $chain, $chainName, $targetName = null) {
        if (!isset($targetName) || !isset($this->chainMapping[$targetName])) {
        // 直接追加到责任链链尾
            if (!isset($this->chainTail)) {
                $this->chainHead = $this->chainTail = $chain;
            } else {
                $this->chainTail->next = $chain;
                $chain->prev           = $this->chainTail;
                $this->chainTail       = $chain;
            }
        } else {
        // 追加到指定节点的后面
            $target = $this->chainMapping[$targetName];

            if (isset($target->next)) {
                $target->next->prev = $chain;
                $chain->next        = $target->next;
            } else { // $target是链尾
                $this->chainTail = $chain;
            }
            $chain->prev = $target;
            $target->next = $chain;
        }
        $this->chainMapping[$chainName] = $chain;
    }

    public function prependChain(\Microbe\Chain $chain, $chainName, $targetName = null) {
        if (!isset($targetName) || !isset($this->chainMapping[$targetName])) {
        // 直接插入到责任链链头
            if (!isset($this->chainHead)) {
                $this->chainHead = $this->chainTail = $chain;
            } else {
                $this->chainHead->prev = $chain;
                $chain->next           = $this->chainHead;
                $this->chainHead       = $chain;
            }
        } else {
        // 插入到指定里程碑的链头
            // 寻找指定里程碑的当前链头
            $target = $this->chainMapping[$targetName];
            if (isset($target->prev)) {
                $target->prev->next = $chain;
                $chain->prev        = $target->prev;
            } else { // $target是链头
                $this->chainHead = $chain;
            }
            $chain->next     = $target;
            $target->prev = $chain;
        }
        $this->chainMapping[$chainName] = $chain;
    }

    public function dumpChain() {
        foreach ($this->chainMapping as $chainName => $chainConfig) {
            echo $chainName . "<br />\n";
        }
    }
}
