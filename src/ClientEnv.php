<?php
namespace Microbe;
class ClientEnv {
    protected $env = [];

    public function __construct() {
    }

    public function setEnv($name, $value) {
        $this->env[$name] = $value;
    }

    /*
    配置格式
    [
        'FeatureName' => [
            'summary' => '摘要描述',
            'rules' => [
                [
                    'EnvName Operator Value',
                    'EnvName Operator Value',
                    'EnvName Operator Value',
                ],
                [
                    'EnvName Operator Value',
                    'EnvName Operator Value',
                    'EnvName Operator Value',
                ],
            ],
        ],
    ]

    EnvName: 命名仅允许[a-zA-Z_-]
    Operator: 所有非空字符均可

    支持的Operator
    正则匹配: match
    空和非空: isEmpty isNotEmpty
    大小比较：eq neq >= <= = > <
    版本比较: v>= v<= v= v> v<  # a.b.c.d 以点分段, 逐段比较大小
    模比较: <=% >=% >% <% =%    # 指定EnvName的值必须为数字, 取模之后进行比较
    集合判定: in notin :in :notin  # 指定EnvName的值是否在后面指定的值列表中
    */
    public function is($featureName) {
        $conf = \Microbe\Microbe::$ins->mainApp->config->get('app.feature.' . $featureName);
        if (empty($conf)) {
            return FALSE;
        }

        $rules = $conf['rules'];
        if (empty($conf)) {
            return FALSE;
        }

        if (!is_array($rules[0])) {
            $rules = [$rules];
        }

        // 多条规则, 表示"或"的关系
        foreach ($rules as $rule) {
            foreach ($rule as $expr) {
                // 规则语法检测 + 信息提取. (存在语法错误则认为规则不成立)
                if (!preg_match(';^(?P<env>[-\w]+)\s+(?P<operator>\S+)(?:\s+(?P<arg>.*))?$;', $expr, $match)) {
                    break;
                }

                $arg      = array_key_exists('arg', $match) ? $match['arg'] : '';
                $envName  = $match['env'];
                $operator = $match['operator'];
                $envValue = $this->env[$envName];

                switch ($operator) {
                    case 'match':
                        if (!preg_match(strval($arg), strval($envValue))) {
                            continue 3;
                        }
                        break;
                    case 'isEmpty':
                        if (!empty($envValue)) {
                            continue 3;
                        }
                        break;
                    case 'isNoEmpty':
                        if (empty($envValue)) {
                            continue 3;
                        }
                        break;
                    case 'eq' :
                        if (strval($envValue) != trim($arg)) {
                            continue 3;
                        }
                        break;
                    case 'neq' :
                        if (strval($envValue) == trim($arg)) {
                            continue 3;
                        }
                        break;
                    case '>=' :
                        if (floatval($envValue) < floatval($arg)) {
                            continue 3;
                        }
                        break;
                    case '<=' :
                        if (floatval($envValue) > floatval($arg)) {
                            continue 3;
                        }
                        break;
                    case '=' :
                        if (floatval($envValue) != floatval($arg)) {
                            continue 3;
                        }
                        break;
                    case '>' :
                        if (floatval($envValue) <= floatval($arg)) {
                            continue 3;
                        }
                        break;
                    case '<' :
                        if (floatval($envValue) >= floatval($arg)) {
                            continue 3;
                        }
                        break;
                    case 'v>=' :
                        if ($this->versionDiff($envValue, $arg) <0) {
                            continue 3;
                        }
                        break;
                    case 'v<=' :
                        if ($this->versionDiff($envValue, $arg) >0) {
                            continue 3;
                        }
                        break;
                    case 'v=' :
                        if ($this->versionDiff($envValue, $arg) !==0) {
                            continue 3;
                        }
                        break;
                    case 'v>' :
                        if ($this->versionDiff($envValue, $arg) <=0) {
                            continue 3;
                        }
                        break;
                    case 'v<' :
                        if ($this->versionDiff($envValue, $arg) >=0) {
                            continue 3;
                        }
                        break;
                    case '<=%': # deviceId <=% 100:10  表示设备id经过运算后的对100取模小于等于10为真
                    case '>=%': # deviceId >=% 100:10  表示设备id经过运算后的对100取模大于等于10为真
                    case '>%':  # deviceId >% 100:10  表示设备id经过运算后的对100取模大于10为真
                    case '<%':  # deviceId <% 100:10  表示设备id经过运算后的对100取模小于10为真
                    case '=%':  # deviceId =% 100:10  表示设备id经过运算后的对100取模等于10为真
                        if (!$this->modularMatch($envValue, $operator, $arg)) {
                            continue 3;
                        }
                        break;
                    case 'in': #in操作, 后面的数据以","分开
                    case ',in':#in操作, 后面的数据以","分开
                    case '.in':#in操作, 后面的数据以"."分开
                    case ':in':#in操作, 后面的数据以":"分开
                    case ';in':#in操作, 后面的数据以";"分开
                    case '/in':#in操作, 后面的数据以"/"分开
                        $seperator = ',';
                        if ($operator[0] != 'i') {
                            $seperator = $seperator[0];
                            $operator  = substr($operator, 1);
                        }
                        $arg = $arg ? trim($arg) : '';
                        $elems = explode($seperator, $arg);
                        if(empty($envValue) || !in_array(trim(strval($envValue)), $elems, TRUE)){
                            continue 3;
                        }
                        break;
                    case 'notin': #notin操作, 后面的数据以","分开
                    case ',notin':#notin操作, 后面的数据以","分开
                    case '.notin':#notin操作, 后面的数据以"."分开
                    case ':notin':#notin操作, 后面的数据以":"分开
                    case ';notin':#notin操作, 后面的数据以";"分开
                    case '/notin':#notin操作, 后面的数据以"/"分开
                        $seperator = ',';
                        if ($operator[0] != 'i') {
                            $seperator = $seperator[0];
                            $operator  = substr($operator, 1);
                        }
                        $arg = $arg ? trim($arg) : '';
                        $elems = explode($seperator, $arg);
                        if(in_array(trim(strval($envValue)), $elems, TRUE)){
                            continue 3;
                        }
                        break;
                    default :
                        continue 3;
                }
            }
            return TRUE;
        }
        return FALSE;
    }

    protected function versionDiff($v1, $v2) {
        $eles1 = explode('.', $v1);
        $eles2 = explode('.', $v2);
        $l1 = count($eles1);
        $l2 = count($eles2);

        $len = max($l1, $l2);
        for ($i = 0; $i < $len; $i++) {
            $p1 = isset($eles1[$i]) ? intval($eles1[$i]) : 0;
            $p2 = isset($eles2[$i]) ? intval($eles2[$i]) : 0;

            if ($p1 > $p2) {
                return 1;
            } elseif ($p1 < $p2) {
                return -1;
            }
        }
        return 0;
    }

    protected function modularMatch($value, $operator, $arg) {
        list($total, $limit) = explode(':', $arg);
        $total = intval($total);
        $limit = intval($limit);
        if (!is_numeric($value)) {
            // 非数字值. 取字符串值, md5, 后16位. 转64位整型
            $value = hexdec(substr(md5(strval($value)), 16)) & 0x7FFFFFFFFFFFFFFF;
        }

        $modular = $value % $total;
        if ($operator == '>=%') {
            return $modular >= $limit;
        } else if ($operator == '<=%') {
            return $modular <= $limit;
        } else if ($operator == '<%') {
            return $modular < $limit;
        } else if ($operator == '>%') {
            return $modular > $limit;
        } else if ($operator == '=%') {
            return $modular == $limit;
        }

        return FALSE;
    }
}
