<?php
namespace Microbe\Config;
class File implements \Microbe\Config {
    protected $confArr;

    public function __construct($rootPath) {
        $this->confArr = self::loadConfig($rootPath);
    }

    /**
     * get
     * 获取配置项
     * @param mixed $key
     * @access public
     * @return void
     */
    public function get($key, $default = NULL) {
        $keyEles = explode('.', $key);
        $confVal = $this->confArr;
        foreach ($keyEles as $keyEle) {
            if (!isset($confVal[$keyEle])) {
                return $default;
            }
            $confVal = $confVal[$keyEle];
        }
        return $confVal;
    }

    /**
     * set
     * 覆盖设置一个配置项
     * @param mixed $key
     * @param mixed $val
     * @access public
     * @return void
     */
    public function set($key, $val) {
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

    protected static function getConfig($file) {
        $__preInclude = get_defined_vars();
        include $file;
        $__postInclude = get_defined_vars();
        unset($__postInclude['__preInclude']);
        return array_diff_key($__postInclude, $__preInclude);
    }
}
