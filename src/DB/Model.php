<?php
namespace Microbe\DB;
abstract class Model {

    protected $tableName;
    protected $dbResource;
    protected $mapping;

    protected $affectedNum;
    protected $lastId;

    const RS_NONE  = 0;
    const RS_ARRAY = 1;
    const RS_NUM   = 2;

    const DUPLICATE_ERRNO = 1062;

    public function getTableName() {
        return $this->tableName;
    }

    public function getLastId() {
        return $this->lastId;
    }

    public function getAffectedNum() {
        return $this->affectedNum;
    }

    public function query($sql, $onReturn = self::RS_ARRAY) {
        $cluster = $this->isMasterSql($sql) ? 'master' : 'slave';
        $dsn     = 'mysqli://' . $this->dbResource . '/' . $cluster;
        $handler = \Microbe\Microbe::$ins->workApp->resourceFactory->get($dsn);

        $rs = $handler->query($sql);
        if ($handler->errno && $handler->errno != self::DUPLICATE_ERRNO) {
            return FALSE;
        }

        switch ($onReturn) {
            case self::RS_NUM:
                $rows = array();
                while ($row = $rs->fetch_row()) {
                    $rows[] = $row;
                }
                return $rows;
            break;
            case self::RS_ARRAY:
                $rows = array();
                while ($row = $rs->fetch_assoc()) {
                    $rows[] = $row;
                }
                return $rows;
            break;
            case self::RS_NONE:
            default:
                $this->affectedNum = $handler->affected_rows;
                $this->lastId      = $handler->insert_id;
                return $rs === FALSE ? FALSE : TRUE;
            break;
        }
    }

    public function execute($sql) {
        return $this->query($sql, self::RS_NONE);
    }


    protected function isMasterSql($sql) {
        return preg_match(';^\s*(?:SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD\s+DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK|START\s+TRANSACTION|COMMIT|ROLLBACK)\s+|/\*\s*MASTER\s*\*/;i', $sql);
    }

    protected function limitClause($limit = FALSE, $offset = 0) {
        $limitClause = [];
        if ($limit > 0) {
            $limitClause['limit'] = $limit;
        }
        if ($offset > 0) {
            $limitClause['offset'] = $offset;
        }
        return $limitClause;
    }

    public function getRows($where = array(), $cols = '*', $limit = FALSE, $offset = 0, $orderBy = FALSE, $join = FALSE, $groupBy = FALSE, $having = FALSE, $tableOptions = FALSE) {
        $sql = \Microbe\DB\Query::buildSelect($this, $where, $cols, $this->limitClause($limit, $offset), $orderBy, $join, $groupBy, $having, $tableOptions);
        return $this->query($sql);
    }

    public function getRow($where = array(), $cols = '*', $orderBy = FALSE, $join = FALSE, $groupBy = FALSE, $having = FALSE, $tableOptions = FALSE, $selectOptions = FALSE) {
        $rows = $this->getRows($where, $cols, 1, 0, $orderBy, $join, $groupBy, $having, $tableOptions, $selectOptions);
        if (!is_array($rows)) {
            return FALSE;
        }
        return count($rows) === 1 ? $rows[0] : array();
    }

    public function count($where = array(), $join = FALSE, $countField = '*', $tableOptions = FALSE) {
        $row = $this->getRow($where, 'count(' . $countField . ') AS count', FALSE, $join, FALSE, FALSE, $tableOptions);
        if (!is_array($row) || empty($row)) {
            return FALSE;
        }
        return intval($row['count']);
    }

    public function update($setValues, $where = array(), $orderBy = FALSE, $limit = FALSE, $tableOptions = FALSE) {
        $sql = \Microbe\DB\Query::buildUpdate($this, $setValues, $where, $this->limitClause($limit), $orderBy, $tableOptions);
        return $this->execute($sql);
    }

    public function insert($value, $onDup = FALSE) {
        $sql = \Microbe\DB\Query::buildInsert($this, $value, $onDup);
        return $this->execute($sql);
    }

    public function multiInsert($values) {
        $sql = \Microbe\DB\Query::buildMultiInsert($this, $values);
        return $this->execute($sql);
    }

    public function replace($value) {
        $sql = \Microbe\DB\Query::buildReplace($this, $value);
        return $this->execute($sql);
    }

    public function multiReplace($values) {
        $sql = \Microbe\DB\Query::buildMultiReplace($this, $values);
        return $this->execute($sql);
    }

    public function delete($where = array(), $limit = FALSE, $orderBy = FALSE) {
        $sql = \Microbe\DB\Query::buildDelete($this, $where, $this->limitClause($limit), $orderBy);
        return $this->execute($sql);
    }

    public function escapeName($fieldName) {
        if (preg_match(';^\w+$;', $fieldName)) {
            return '`' . $fieldName . '`';
        } else {
            return $fieldName;
        }
    }

    public function escapeValue($value, $name = '') {
        if (!array_key_exists($name, $this->mapping) || !in_array($this->mapping[$name], ['i', 'f', 's'])) {
            if (is_int($value)) {
                return $this->escapeInt($value);
            } else if (is_float($value)) {
                return $this->escapeFloat($value);
            } else {
                return $this->escapeString(strval($value));
            }
        }
        switch ($this->mapping[$name]) {
            case 'i': // 整型
                return $this->escapeInt($value);
            case 'f': // 浮点
                return $this->escapeFloat($value);
            case 's': // 普通字符串
                return $this->escapeString($value);
        }
    }

    public function escapeString($value) {
        return "'" . addslashes((string)$value) . "'"; # add slashes for ('), ("), (\), (NULL byte)
    }

    public function escapeString4Like($value) {
        return addcslashes((string)$value, "\\\"'\0%_"); # add slashes for ('), ("), (\), (NULL byte), (%), (_) && don't add quote
    }

    public function escapeInt($value) {
        return (int)$value;
    }

    public function escapeFloat($value) {
        return (float)$value;
    }

}
