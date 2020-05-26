<?php
namespace Microbe;
class Microbe {
    public $config;

    public $chainHead;
    public $chainTail;

    protected $milestones = [];

    public static $ins;

    protected function __construct() {
    }

    public static function init(\Microbe\Config $config) {
        if (isset(self::$ins)) {
            return ;
        }
        self::$ins         = new self();
        self::$ins->config = $config;
    }

    public function milestone(\Microbe\Chain $chain, $milestoneName) {
        $chain->markMilestone($milestoneName);
        $this->appendChain($chain);
        $this->milestones[$milestoneName] = $chain;

        /* 用户自定义的责任链安装 */
        $userChains = $this->config->get('milestone.' . $milestoneName);
        if (empty($userChains)) {
            return;
        }
        foreach ($userChains as $chain) {
            $class  = $chain['class'];
            $config = $chain['config'];
            $this->prependChain(new $class($config), $milestoneName);
        }
    }

    public function appendChain(\Microbe\Chain $chain, $milestoneName = null) {
        if (!isset($milestoneName) || !isset($this->milestones[$milestoneName])) {
        // 直接追加到责任链链尾
            if (!isset($this->chainTail)) {
                $this->chainHead = $this->chainTail = $chain;
            } else {
                $this->chainTail->next = $chain;
                $chain->prev           = $this->chainTail;
                $this->chainTail       = $chain;
            }
        } else {
        // 追加到指定里程碑的链尾
            $milestone = $this->milestones[$milestoneName];
            if (isset($milestone->prev)) {
                $milestone->prev->next = $chain;
                $chain->prev = $milestone->prev;
            }
            $chain->next     = $milestone;
            $milestone->prev = $chain;

            if ($chain->prev == null) {
                $this->chainHead = $chain;
            }
        }
    }

    public function prependChain(\Microbe\Chain $chain, $milestoneName = null) {
        if (!isset($milestoneName) || !isset($this->milestones[$milestoneName])) {
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
            $tmpChain = $this->milestones[$milestoneName];
            while (isset($tmpChain->prev) && !$tmpChain->prev->isMilestone()){
                $tmpChain = $tmpChain->prev;
            }
            // 插入
            if (isset($tmpChain->prev)) {
                $tmpChain->prev->next = $chain;
                $chain->prev = $tmpChain->prev;
            }
            $chain->next     = $tmpChain;
            $tmpChain->prev = $chain;

            if ($chain->prev == null) {
                $this->chainHead = $chain;
            }
        }
    }
}
