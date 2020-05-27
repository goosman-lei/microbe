<?php
namespace Microbe\Config;
class File implements \Microbe\Config {

    protected $confArr;

    public function __construct($rootPath) {
        $this->confArr = self::loadConfig($rootPath);
    }

    /**
     * get 
     * 
     * @param mixed $key  A.B.C
     * @param mixed $default 
     * @access public
     * @return void
     */
    public function get($key, $default = null) {
        $keyEles = explode('.', $key);
        $confValue = $this->confArr;
        foreach ($keyEles as $keyEle) {
            if (!isset($confValue[$keyEle])) {
                return $default;
            }
            $confValue = $confValue[$keyEle];
        }
        return $confValue;
    }
    public function set($key, $value) {
        $keyEles = explode('.', $key);
        $confRef = &$this->confArr;
        foreach ($keyEles as $keyEle) {
            if (!is_array($confRef[$keyEle])) {
                $confRef[$keyEle] = array();
            }
            $confRef = &$confRef[$keyEle];
        }
        $confRef = $val;
    }

    /**
     * loadConfig 
     * 处理递归问题, 解决目录遍历
     * @param mixed $rootPath 
     * @static
     * @access protected
     * @return void
     */
    protected static function loadConfig($rootPath) {
        if (is_file($rootPath)) {
            return self::getConfig($rootPath);
        } else if (!is_dir($rootPath)) {
            return [];
        }

        $confArr = [];
        $dp = opendir($rootPath);
        while ($fname = readdir($dp)) {
            $fpath = "{$rootPath}/{$fname}";
            if ($fname == '.' || $fname == '..') {
                continue;
            } else if (is_dir($fpath)) {
                $confArr[$fname] = self::loadConfig($fpath);
            } else if (is_file($fpath) && substr($fpath, - 4) === '.php') {
                $confKey = preg_replace(';\.php$;i', '', $fname);

                $confArr[$confKey] = self::getConfig($fpath);
            }
        }
        return $confArr;
    }

    /**
     * getConfig 
     * 获取一个php文件中的所有变量
     * @param mixed $file 
     * @static
     * @access protected
     * @return void
     */
    protected static function getConfig($file) {
        $__preInclude = get_defined_vars();
        include $file;
        $__postInclude = get_defined_vars();
        unset($__postInclude['__preInclude']);
        return array_diff_key($__postInclude, $__preInclude);
    }
}
