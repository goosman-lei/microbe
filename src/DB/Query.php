<?php
namespace Microbe\DB;
class Query {
    protected static $model;

    public static function buildSelect($model, $whereClause, $selectClause, $limitClause, $orderByClause, $joinClause, $groupByClause, $havingClause, $tableOptionClause) {
        self::$model = $model;
        $sql = 'SELECT';

        if (empty($selectClause)) {
            $selectClause = '*';
        }
        $sql .= ' ' . self::buildClauseSimpleImplode($selectClause);

        $sql .= ' FROM ' . self::buildTableInfo($tableOptionClause);

        if (!empty($joinClause)) {
            $sql .= ' ' . self::buildClauseJoin($joinClause);
        }

        if (!empty($whereClause)) {
            $sql .= ' WHERE ' . self::buildClauseCondition($whereClause);
        }

        if (!empty($groupByClause)) {
            $sql .= ' GROUP BY ' . self::buildClauseSimpleImplode($groupByClause);
        }

        if (!empty($havingClause)) {
            $sql .= ' HAVING ' . self::buildClauseCondition($havingClause);
        }

        if (!empty($orderByClause)) {
            $sql .= ' ORDER BY ' . self::buildClauseSimpleImplode($orderByClause);
        }

        if (!empty($limitClause)) {
            $sql .= ' ' . self::buildClauseLimit($limitClause);
        }

        return $sql;
    }

    public static function buildUpdate($model, $setClause, $whereClause, $limitClause, $orderByClause, $tableOptionClause) {
        self::$model = $model;
        $sql = 'UPDATE';

        $sql .= ' ' . self::buildTableInfo($tableOptionClause);

        $sql .= ' SET ' . self::buildClauseSet($setClause);

        if (!empty($whereClause)) {
            $sql .= ' WHERE ' . self::buildClauseCondition($whereClause);
        }

        if (!empty($orderByClause)) {
            $sql .= ' ORDER BY ' . self::buildClauseSimpleImplode($orderByClause);
        }

        if (!empty($limitClause)) {
            $sql .= ' ' . self::buildClauseLimit($limitClause);
        }

        return $sql;
    }

    public static function buildReplace($model, $value) {
        self::$model = $model;
        $sql = 'REPLACE';

        $sql .= ' ' . self::buildTableInfo();

        list($columnClauseString, $valueClauseString) = self::buildClauseValue($value);
        $sql .= ' ' . $columnClauseString . ' VALUES ' . $valueClauseString;

        return $sql;
    }

    public static function buildMultiReplace($model, $values) {
        self::$model = $model;
        $sql = 'REPLACE';

        $sql .= ' ' . self::buildTableInfo();

        list($columnClauseString, $valuesClauseString) = self::buildClauseValues($values);
        $sql .= ' ' . $columnClauseString . ' VALUES ' . $valuesClauseString;

        return $sql;
    }

    public static function buildInsert($model, $value, $onDupClause) {
        self::$model = $model;
        $sql = 'INSERT INTO';

        $sql .= ' ' . self::buildTableInfo();

        list($columnClauseString, $valueClauseString) = self::buildClauseValue($value);
        $sql .= ' ' . $columnClauseString . ' VALUES ' . $valueClauseString;

        if (!empty($onDupClause)) {
            $sql .= ' ON DUPLICATE KEY UPDATE ' . self::buildClauseOnDup($onDupClause, $value);
        }

        return $sql;
    }

    public static function buildMultiInsert($model, $values) {
        self::$model = $model;
        $sql = 'INSERT INTO';

        $sql .= ' ' . self::buildTableInfo();

        list($columnClauseString, $valuesClauseString) = self::buildClauseValues($values);
        $sql .= ' ' . $columnClauseString . ' VALUES ' . $valuesClauseString;

        return $sql;
    }

    public static function buildDelete($model, $whereClause, $limitClause, $orderByClause) {
        self::$model = $model;
        $sql = 'DELETE FROM';

        $sql .= ' ' . self::buildTableInfo();

        if (!empty($whereClause)) {
            $sql .= ' WHERE ' . self::buildClauseCondition($whereClause);
        }

        if (!empty($orderByClause)) {
            $sql .= ' ORDER BY ' . self::buildClauseSimpleImplode($orderByClause);
        }

        if (!empty($limitClause)) {
            $sql .= ' ' . self::buildClauseLimit($limitClause);
        }

        return $sql;
    }

    /**
     * buildClauseSimpleImplode 
     * 
     * @param mixed $clause 
        format 1: 字符串形式, 直接采用字面
            'column1, column2 AS alias'
        format 2: 数组形式, 使用implode(', ')连接;
            [
                'column1',
                'column2 AS alias',
            ]
     * @access protected
     * @return void
     */
    protected static function buildClauseSimpleImplode($clause) {
        if (is_array($clause)) {
            return implode(', ', $clause);
        }
        return strval($clause);
    }

    /**
     * buildClauseOnDup 
     * 
     * @param mixed $onDupClause 
        format 1: 字符串形式, 直接采用原文
            'field1 = xxx, field2 = xxx'
        format 2: 数组形式, 指定从insert into的value中提取值
     * @access protected
     * @return void
     */
    protected static function buildClauseOnDup($onDupClause, $value = []) {
        if (is_string($onDupClause)) {
            return $onDupClause;
        }
        if (is_array($onDupClause)) {
            $rStringArr = [];
            foreach ($onDupClause as $field) {
                $rStringArr[] = sprintf('%s = %s', self::$model->escapeName($field), self::$model->escapeValue($value[$field], $field));
            }
            return implode(', ', $rStringArr);
        }
        return '';
    }

    /**
     * buildClauseJoin 
     * 
     * @param mixed $joinClause 
        Common: 没有指定JOIN的具体形式, 则默认JOIN
        format 1: 字符串形式, 直接套入SQL
            'table1 AS t1 ON t1.id = xxx.xxx'
        format 2: 数组形式, 直接implode(' ')
            [
                'table1 AS t1 ON t1.id = xxx.xxx',
                'LEFT JOIN table2 AS t2 ON t1.uid = t2.uid',
            ]
     * @access protected
     * @return void
     */
    protected static function buildClauseJoin($joinClause) {
        $rString = '';
        if (is_array($joinClause)) {
            $joinStrArr = array();
            foreach ($joinClause as $joinStr) {
                if (!preg_match(';^\s*(?:(?:left|right)(?:\s+outer)?|inner)?\s*join\s+;i', $joinStr, $match)) {
                    $joinStr = 'JOIN ' . $joinStr;
                }
                $joinStrArr[] = $joinStr;
            }
            $rString = implode(' ', $joinStrArr);
        } else if (is_string($joinClause)) {
            if (!preg_match(';^\s*(?:(?:left|right)(?:\s+outer)?|inner)?\s*join\s+;i', $joinClause)) {
                $rString = 'JOIN ' . $joinClause;
            } else {
                $rString = $joinClause;
            }
        }
        return $rString;
    }

    /**
     * buildClauseLimit 
     * 
     * @param mixed $limitClause 
        format: 数组指定limit和offset. 因为offset可选
            [
                'limit'  => 1,
                'offset' => 2,
            ]
     * @access protected
     * @return void
     */
    protected static function buildClauseLimit($limitClause) {
        $limitClauseString = '';
        if (array_key_exists('limit', $limitClause)) {
            $limitClauseString .= 'LIMIT ' . intval($limitClause['limit']);
        }
        if (array_key_exists('offset', $limitClause)) {
            if (!empty($limitClauseString)) {
                $limitClauseString .= ' ';
            }
            $limitClauseString .= 'OFFSET ' . intval($limitClause['offset']);
        }
        return $limitClauseString;
    }

    /**
     * buildTableInfo 
     * 
     * @param mixed $tableOptionClause 
        format:
        [
          ‘alias’                    => # 别名
          ‘use_index_for_join’       => # USE INDEX FOR JOIN ( <arg-val> )
          ‘use_index_for_orderby’    => # USE INDEX FOR ORDER BY ( <arg-val> )
          ‘use_index_for_groupby’    => # USE INDEX FOR GROUP BY ( <arg-val> )
          ‘ignore_index_for_join’    => # IGNORE INDEX FOR JOIN ( <arg-val> )
          ‘ignore_index_for_orderby’ => # IGNORE INDEX FOR ORDER BY ( <arg-val> )
          ‘ignore_index_for_groupby’ => # IGNORE INDEX FOR GROUP BY ( <arg-val> )
          ‘force_index_for_join’     => # FORCE INDEX FOR JOIN ( <arg-val> )
          ‘force_index_for_orderby’  => # FORCE INDEX FOR ORDER BY ( <arg-val> )
          ‘force_index_for_groupby’  => # FORCE INDEX FOR GROUP BY ( <arg-val> )
        ]
     * @access protected
     * @return void
     */
    protected static function buildTableInfo($tableOptionClause = []) {
        $rString = self::$model->getTableName();
        if (!empty($tableOptionClause)) {
            if (isset($tableOptionClause['alias'])) {
                $rString .= ' AS ' . $tableOptionClause['alias'];
            }
            if (isset($tableOptionClause['use_index_for_join'])) {
                $rString .= ' USE INDEX FOR JOIN (' . $tableOptionClause['use_index_for_join'] . ')';
            }
            if (isset($tableOptionClause['use_index_for_orderby'])) {
                $rString .= ' USE INDEX FOR ORDER BY (' . $tableOptionClause['use_index_for_orderby'] . ')';
            }
            if (isset($tableOptionClause['use_index_for_groupby'])) {
                $rString .= ' USE INDEX FOR GROUP BY (' . $tableOptionClause['use_index_for_groupby'] . ')';
            }
            if (isset($tableOptionClause['ignore_index_for_join'])) {
                $rString .= ' IGNORE INDEX FOR JOIN (' . $tableOptionClause['ignore_index_for_join'] . ')';
            }
            if (isset($tableOptionClause['ignore_index_for_orderby'])) {
                $rString .= ' IGNORE INDEX FOR ORDER BY (' . $tableOptionClause['ignore_index_for_orderby'] . ')';
            }
            if (isset($tableOptionClause['ignore_index_for_groupby'])) {
                $rString .= ' IGNORE INDEX FOR GROUP BY (' . $tableOptionClause['ignore_index_for_groupby'] . ')';
            }
            if (isset($tableOptionClause['force_index_for_join'])) {
                $rString .= ' FORCE INDEX FOR JOIN (' . $tableOptionClause['force_index_for_join'] . ')';
            }
            if (isset($tableOptionClause['force_index_for_orderby'])) {
                $rString .= ' FORCE INDEX FOR ORDER BY (' . $tableOptionClause['force_index_for_orderby'] . ')';
            }
            if (isset($tableOptionClause['force_index_for_groupby'])) {
                $rString .= ' FORCE INDEX FOR GROUP BY (' . $tableOptionClause['force_index_for_groupby'] . ')';
            }
        }
        return $rString;
    }

    /**
     * buildClauseSet 
     * 
     * @param mixed $setClause 
        format: 1) ASSOC形式代表"字段名-字段值"; 2) INDEX形式代表字面值
            [
                'field1' => 'value',
                'field2' => 'value',
                'age = age + 1',
                'score = score + 1',
            ]
     * @access protected
     * @return void
     */
    protected static function buildClauseSet($setClause) {
        $rStringArr = [];
        foreach ($setClause as $key => $val) {
            if (is_int($key)) {
                $rStringArr[] = $val;
            } else {
                $rStringArr[] = sprintf('%s = %s', self::$model->escapeName($key), self::$model->escapeValue($val, $key));
            }
        }
        return implode(', ', $rStringArr);
    }

    /**
     * buildClauseValue 
     * 
     * @param mixed $value 
        format: ASSOC形式的字段名-字段值
            [
                'field1' => 'value',
                'field2' => 'value',
            ]
     * @access protected
     * @return void
     */
    protected static function buildClauseValue($value) {
        $rColumns = [];
        $rValues  = [];
        foreach ($value as $key => $val) {
            $rColumns[] = self::$model->escapeName($key);
            $rValues[]  = self::$model->escapeValue($val, $key);
        }
        return [
            '(' . implode(', ', $rColumns) . ')',
            '(' . implode(', ', $rValues) . ')',
        ];
    }

    /**
     * buildClauseValues 
     * 
     * @param mixed $values 
        format: 二维ASSOC形式的字段名-字段值
        [
            [
                'field1' => 'value',
                'field2' => 'value',
            ],
            [
                'field1' => 'value',
                'field2' => 'value',
            ],
        ]
     * @access protected
     * @return void
     */
    protected static function buildClauseValues($values) {
        $rColumns = [];
        $rValues  = [];

        $tmpValue = [];
        foreach ($values[0] as $key => $val) {
            $rColumns[] = self::$model->escapeName($key);
            $tmpValue[] = self::$model->escapeValue($val, $key);
        }
        $rValues[] = '(' . implode(', ', $tmpValue) . ')';

        for ($i = 1; $i < count($values); $i ++) {
            $tmpValue = [];
            foreach ($values[$i] as $key => $val) {
                $tmpValue[] = self::$model->escapeValue($val, $key);
            }
            $rValues[] = '(' . implode(', ', $tmpValue) . ')';
        }

        return [
            '(' . implode(', ', $rColumns) . ')',
            implode(', ', $rValues),
        ];

    }

    /**
     * buildClauseCondition 
     * 
     * @param mixed $conds 
        format:
            核心语法: [操作符, 操作数, ...]
            操作符及用法:
                比较操作符:
                    [':ne',         字段名, 值]             字段名 != 值
                    [':!=',         字段名, 值]
                    [':gt',         字段名, 值]             字段名 > 值
                    [':>',          字段名, 值]
                    [':ge',         字段名, 值]             字段名 <= 值
                    [':>=',         字段名, 值]
                    [':eq',         字段名, 值]             字段名 = 值
                    [':=',          字段名, 值]
                    [':le',         字段名, 值]             字段名 <= 值
                    [':<=',         字段名, 值]
                    [':lt',         字段名, 值]             字段名 < 值
                    [':<',          字段名, 值]
                Like及其特例:
                    [':like',       字段名, 值]             字段名 LIKE '值'
                    [':begin',      字段名, 值]             字段名 LIKE '值%'
                    [':end',        字段名, 值]             字段名 LIKE '%值'
                    [':include',    字段名, 值]             字段名 LIKE '%值%'
                    [':notlike',    字段名, 值]             字段名 NOT LIKE '值'
                    [':notbegin',   字段名, 值]             字段名 NOT LIKE '值%'
                    [':notend',     字段名, 值]             字段名 NOT LIKE '%值'
                    [':exclude',    字段名, 值]             字段名 NOT LIKE '%值%'
                三元操作符:
                    [':between',    字段名, 值A, 值B]       字段名 BETWEEN 值A AND 值B
                集合操作符:
                    [':in',         字段名, [值A, 值B]]     字段名 IN (值A, 值B)
                    [':notin',      字段名, [值A, 值B]]     字段名 NOT IN (值A, 值B)
                空值检测操作符:
                    [':isnull',     字段名]                 字段名 IS NULL
                    [':notnull',    字段名]                 字段名 IS NOT NULL
                逻辑操作符:
                    [':not',        表达式列表]             AND NOT (表达式列表解析结果)
                    [':and',        表达式列表]             AND (表达式列表解析结果)
                    [':or',         表达式列表]             OR (表达式列表解析结果)
                原始SQL文本:
                    [':literal',    原始SQL]                原始SQL
            惯用法:
                [字段名, 非数组值]                          字段名 eq 值
                [字段名, 数组值]                            字段名 IN (数组值)
            惯用法:
                标准语法为二维数组:
                    [
                        [操作符, 操作数, ...],
                        [操作符, 操作数, ...],
                        [操作符, 操作数, ...],
                        [操作符, 操作数, ...],
                    ]
                只有单个条件可传递一维数组:
                    [操作符, 操作数, ...],
     * @access protected
     * @return void
     */
    protected static function buildClauseCondition($conds) {
        if (is_string($conds)) {
            return $conds;
        }
        if (!is_array($conds[0])) {
            $conds = [$conds];
        }

        foreach ($conds as $cond) {
            if ($cond[0][0] === ':') {
                $operator = array_shift($cond);
                $operator = strtolower(substr($operator, 1));
            } else {
                $operator = is_array($cond[1]) ? 'in' : 'eq';
            }

            $conj = 'AND';
            switch ($operator) {
                /* 逻辑运算 */
                case 'or':
                    $conj = 'OR';
                    $exprStr = self::buildClauseCondition($cond[0]);
                break;
                case 'and':
                    $conj = 'AND';
                    $exprStr = self::buildClauseCondition($cond[0]);
                break;
                case 'not':
                    $conj = 'AND NOT';
                    $exprStr = self::buildClauseCondition($cond[0]);
                break;
                /* 单目无类型运算符 */
                case 'isnull':
                    $exprStr = self::$model->escapeName($cond[0]) . ' IS NULL';
                break;
                case 'notnull':
                    $exprStr = self::$model->escapeName($cond[0]) . ' IS NOT NULL';
                break;
                case 'literal':
                    $exprStr = $cond[0];
                break;
                /* 双目运算 */
                case 'ne':
                case '!=':
                    $exprStr = self::$model->escapeName($cond[0]) . ' != ' . self::$model->escapeValue($cond[1], $cond[0]);
                break;
                case 'gt':
                case '>':
                    $exprStr = self::$model->escapeName($cond[0]) . ' > ' . self::$model->escapeValue($cond[1], $cond[0]);
                break;
                case 'ge':
                case '>=':
                    $exprStr = self::$model->escapeName($cond[0]) . ' >= ' . self::$model->escapeValue($cond[1], $cond[0]);
                break;
                case 'eq':
                case '=':
                    $exprStr = self::$model->escapeName($cond[0]) . ' = ' . self::$model->escapeValue($cond[1], $cond[0]);
                break;
                case 'le':
                case '<=':
                    $exprStr = self::$model->escapeName($cond[0]) . ' <= ' . self::$model->escapeValue($cond[1], $cond[0]);
                break;
                case 'lt':
                case '<':
                    $exprStr = self::$model->escapeName($cond[0]) . ' < ' . self::$model->escapeValue($cond[1], $cond[0]);
                break;
                case 'like':
                    $exprStr = self::$model->escapeName($cond[0]) . ' LIKE ' . self::$model->escapeValue($cond[1], $cond[0]);
                break;
                case 'begin':
                    $exprStr = self::$model->escapeName($cond[0]) . " LIKE '" . self::$model->escapeString4Like($cond[1]) . "%'";
                break;
                case 'end':
                    $exprStr = self::$model->escapeName($cond[0]) . " LIKE '%" . self::$model->escapeString4Like($cond[1]) . "'";
                break;
                case 'include':
                    $exprStr = self::$model->escapeName($cond[0]) . " LIKE '%" . self::$model->escapeString4Like($cond[1]) . "%'";
                break;
                case 'notlike':
                    $exprStr = self::$model->escapeName($cond[0]) . " NOT LIKE " . self::$model->escapeValue($cond[1], $cond[0]);
                break;
                case 'notbegin':
                    $exprStr = self::$model->escapeName($cond[0]) . " NOT LIKE '" . self::$model->escapeString4Like($cond[1]) . "%'";
                break;
                case 'notend':
                    $exprStr = self::$model->escapeName($cond[0]) . " NOT LIKE '%" . self::$model->escapeString4Like($cond[1]) . "'";
                break;
                case 'exclude':
                    $exprStr = self::$model->escapeName($cond[0]) . " NOT LIKE '%" . self::$model->escapeString4Like($cond[1]) . "%'";
                break;
                /* 三目运算 */
                case 'between':
                    $exprStr = self::$model->escapeName($cond[0]) . ' BETWEEN ' . self::$model->escapeValue($cond[1], $cond[0]) . ' AND ' . self::$model->escapeValue($cond[2], $cond[0]);
                break;
                /* 集合运算 */
                case 'in':
                    if (!is_array($cond[1]) || empty($cond[1])) {
                        $exprStr = self::$model->escapeName($cond[0]) . ' IN (null)';
                        break;
                    }
                    foreach ($cond[1] as $idx => $val) {
                        $cond[1][$idx] = self::$model->escapeValue($val, $cond[0]);
                    }
                    $exprStr = self::$model->escapeName($cond[0]) . ' IN (' . implode(', ', $cond[1]) . ')';
                break;
                case 'notin':
                    if (!is_array($cond[1]) || empty($cond[1])) {
                        $exprStr = self::$model->escapeName($cond[0]) . ' NOT IN (null)';
                        break;
                    }
                    foreach ($cond[1] as $idx => $val) {
                        $cond[1][$idx] = self::$model->escapeValue($val, $cond[0]);
                    }
                    $exprStr = self::$model->escapeName($cond[0]) . ' NOT IN (' . implode(', ', $cond[1]) . ')';
                break;
            }
            $exprArr[] = $conj . ' (' . $exprStr . ')';
        }
        return preg_replace(';^\w+\s+;', '', implode(' ', $exprArr)); // 去掉开始的连词
    }
}