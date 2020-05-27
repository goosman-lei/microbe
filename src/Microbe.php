<?php
namespace Microbe;
class Microbe {
    public $config;

    public $chainHead;
    public $chainTail;

    protected $chainMapping = [];

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

    public function doChain($request, $response) {
        $this->chainHead->exec($request, $response);
    }

    /**
     * installUserChain 
     * 用户定义的责任链配置:
     $chains = [
        'antispam' => [    # KEY为节点的名字. 用户自定义的名字, 不允许以"-"开始. 系统定义的名字, 全部以"-"开始
            'class'     => # 责任链的处理类
            'config'    => # 责任链的具体配置
            'direction' => # 责任链添加的方向: append or prepend
            'target'    => # 责任链要添加的相对位置. 默认为空, 代表整条链的首尾
        ],
     ];
     * @access public
     * @return void
     */
    public function installUserChain() {
        /* 用户自定义的责任链安装 */
        $userChains = $this->config->get('framework.chain');
        if (empty($userChains)) {
            return;
        }
        foreach ($userChains as $chainName => $chainInfo) {
            if ($chainName[0] == '-') {
                throw new RuntimeException("Invalid user chain name[{$chainName}], it shouldn't begin with '-'");
            }
            $class     = $chainInfo['class'];
            $config    = $chainInfo['config'];
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
}
