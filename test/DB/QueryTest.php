<?php
use \PHPUnit\Framework\TestCase;
final class QueryTest extends TestCase {
    public function testBuildDelete() {
        // buildDelete($model, $whereClause, $limitClause, $orderByClause);
        $userModel = new UserModel();

        $rString = \Microbe\DB\Query::buildDelete(
            $userModel,
            [':gt', 'age', 10],
            ['limit' => 10],
            'score');
        $expectedSql = 'DELETE FROM user';
            $expectedSql .= ' WHERE (`age` > 10)';
            $expectedSql .= ' ORDER BY score';
            $expectedSql .= ' LIMIT 10';
        $this->assertEquals($rString, $expectedSql);
    }

    public function testBuildMultiInsert() {
        // buildMultiInsert($model, $values);
        $userModel = new UserModel();

        $rString = \Microbe\DB\Query::buildMultiInsert(
            $userModel,
            [
                [
                    'id'    => 10,
                    'name'  => 'Jack',
                    'score' => 88,
                ],
                [
                    'id'    => 11,
                    'name'  => 'Tom',
                    'score' => 90,
                ],
                [
                    'id'    => 12,
                    'name'  => 'Lucas',
                    'score' => 98,
                ],
            ]);
        $expectedSql = 'INSERT INTO user';
            $expectedSql .= ' (`id`, `name`, `score`)';
            $expectedSql .= ' VALUES (10, \'Jack\', 88),';
                $expectedSql .= ' (11, \'Tom\', 90),';
                $expectedSql .= ' (12, \'Lucas\', 98)';
        $this->assertEquals($rString, $expectedSql);
    }

    public function testBuildInsert() {
        // buildInsert($model, $value);
        $userModel = new UserModel();

        $rString = \Microbe\DB\Query::buildInsert(
            $userModel,
            [
                'id'    => 10,
                'name'  => 'Jack',
                'score' => 88,
            ],
            FALSE);
        $expectedSql = 'INSERT INTO user';
            $expectedSql .= ' (`id`, `name`, `score`)';
            $expectedSql .= ' VALUES (10, \'Jack\', 88)';
        $this->assertEquals($rString, $expectedSql);

        /* onDup直接写 */
        $rString = \Microbe\DB\Query::buildInsert(
            $userModel,
            [
                'id'    => 10,
                'name'  => 'Jack',
                'score' => 88,
            ],
            'score = 88');
        $expectedSql = 'INSERT INTO user';
            $expectedSql .= ' (`id`, `name`, `score`)';
            $expectedSql .= ' VALUES (10, \'Jack\', 88)';
            $expectedSql .= ' ON DUPLICATE KEY UPDATE score = 88';
        $this->assertEquals($rString, $expectedSql);

        /* onDup从value中提取 */
        $rString = \Microbe\DB\Query::buildInsert(
            $userModel,
            [
                'id'    => 10,
                'name'  => 'Jack',
                'score' => 88,
            ],
            ['name', 'score']);
        $expectedSql = 'INSERT INTO user';
            $expectedSql .= ' (`id`, `name`, `score`)';
            $expectedSql .= ' VALUES (10, \'Jack\', 88)';
            $expectedSql .= ' ON DUPLICATE KEY UPDATE `name` = \'Jack\', `score` = 88';
        $this->assertEquals($rString, $expectedSql);

    }

    public function testBuildMultiReplace() {
        // buildMultiReplace($model, $values);
        $userModel = new UserModel();

        $rString = \Microbe\DB\Query::buildMultiReplace(
            $userModel,
            [
                [
                    'id'    => 10,
                    'name'  => 'Jack',
                    'score' => 88,
                ],
                [
                    'id'    => 11,
                    'name'  => 'Tom',
                    'score' => 90,
                ],
                [
                    'id'    => 12,
                    'name'  => 'Lucas',
                    'score' => 98,
                ],
            ]);
        $expectedSql = 'REPLACE user';
            $expectedSql .= ' (`id`, `name`, `score`)';
            $expectedSql .= ' VALUES (10, \'Jack\', 88),';
                $expectedSql .= ' (11, \'Tom\', 90),';
                $expectedSql .= ' (12, \'Lucas\', 98)';
        $this->assertEquals($rString, $expectedSql);
    }

    public function testBuildReplace() {
        // buildReplace($model, $value);
        $userModel = new UserModel();

        $rString = \Microbe\DB\Query::buildReplace(
            $userModel,
            [
                'id'    => 10,
                'name'  => 'Jack',
                'score' => 88,
            ]);
        $expectedSql = 'REPLACE user';
            $expectedSql .= ' (`id`, `name`, `score`)';
            $expectedSql .= ' VALUES (10, \'Jack\', 88)';
        $this->assertEquals($rString, $expectedSql);
    }

    public function testBuildUpdate() {
        // buildUpdate($model, $setClause, $whereClause, $limitClause, $orderByClause, $tableOptionClause);
        $userModel = new UserModel();

        $rString = \Microbe\DB\Query::buildUpdate(
            $userModel,
            [
                'name' => 'Jack',
                'score' => 88,
                'age = age + 1',
            ],
            ['id', 10],
            ['limit' => 10, 'offset' => 8],
            ['age desc', 'gender'],
            ['alias' => 'u']);
        $expectedSql = 'UPDATE user AS u';
            $expectedSql .= ' SET';
                $expectedSql .= ' `name` = \'Jack\',';
                $expectedSql .= ' `score` = 88,';
                $expectedSql .= ' age = age + 1';
            $expectedSql .= ' WHERE (`id` = 10)';
            $expectedSql .= ' ORDER BY age desc, gender';
            $expectedSql .= ' LIMIT 10 OFFSET 8';
        $this->assertEquals($rString, $expectedSql);
    }

    public function testBuildSelectOtherClause() {
        // buildSelect($model, $whereClause, $selectClause, $limitClause, $orderByClause, $joinClause, $groupByClause, $havingClause, $tableOptionClause);
        $userModel = new UserModel();

        $rString = \Microbe\DB\Query::buildSelect(
            $userModel,
            ['name', 'Jack'],
            'id, name, gender',
            ['limit' => 10, 'offset' => 8],
            ['age desc', 'gender'],
            'account AS a ON u.id = a.uid',
            'age',
            [':literal', 'AVG(score) > 80'],
            ['alias' => 'u']);
        $expectedSql = 'SELECT id, name, gender';
            $expectedSql .= ' FROM user AS u';
            $expectedSql .= ' JOIN account AS a ON u.id = a.uid';
            $expectedSql .= ' WHERE (`name` = \'Jack\')';
            $expectedSql .= ' GROUP BY age';
            $expectedSql .= ' HAVING (AVG(score) > 80)';
            $expectedSql .= ' ORDER BY age desc, gender';
            $expectedSql .= ' LIMIT 10 OFFSET 8';
        $this->assertEquals($rString, $expectedSql);
    }

    public function testBuildSelectWhereClause() {
        // buildSelect($model, $whereClause, $selectClause, $limitClause, $orderByClause, $joinClause, $groupByClause, $havingClause, $tableOptionClause);
        $userModel = new UserModel();

        /* 基础用法 */
        $rString = \Microbe\DB\Query::buildSelect($userModel, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE);
        $this->assertEquals($rString, 'SELECT * FROM user');

        /*
        惯用法:
            [字段名, 非数组值]                          字段名 eq 值
            [字段名, 数组值]                            字段名 IN (数组值)
        */
        $rString = \Microbe\DB\Query::buildSelect($userModel, ['id', 1], FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE);
        $this->assertEquals($rString, 'SELECT * FROM user WHERE (`id` = 1)');

        $rString = \Microbe\DB\Query::buildSelect($userModel, ['name', 'Jack\'"'], FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE);
        $this->assertEquals($rString, 'SELECT * FROM user WHERE (`name` = \'Jack\\\'\\"\')');

        $rString = \Microbe\DB\Query::buildSelect($userModel, ['name', ['Jack\'"', 'Tom', 123]], FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE);
        $this->assertEquals($rString, 'SELECT * FROM user WHERE (`name` IN (\'Jack\\\'\\"\', \'Tom\', \'123\'))');

        /*
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
        */
        $rString = \Microbe\DB\Query::buildSelect($userModel, [
            [':ne', 'id', 123],
            [':!=', 'name', 123],
            [':gt', 'id', 123],
            [':>', 'name', 123],
            [':ge', 'id', 123],
            [':>=', 'name', 123],
            [':eq', 'id', 123],
            [':=', 'name', 123],
            [':le', 'id', 123],
            [':<=', 'name', 123],
            [':lt', 'id', 123],
            [':<', 'name', 123],
        ], FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE);
        $expectedSql = 'SELECT * FROM user WHERE';
            $expectedSql .= ' (`id` != 123)';
            $expectedSql .= ' AND (`name` != \'123\')';
            $expectedSql .= ' AND (`id` > 123)';
            $expectedSql .= ' AND (`name` > \'123\')';
            $expectedSql .= ' AND (`id` >= 123)';
            $expectedSql .= ' AND (`name` >= \'123\')';
            $expectedSql .= ' AND (`id` = 123)';
            $expectedSql .= ' AND (`name` = \'123\')';
            $expectedSql .= ' AND (`id` <= 123)';
            $expectedSql .= ' AND (`name` <= \'123\')';
            $expectedSql .= ' AND (`id` < 123)';
            $expectedSql .= ' AND (`name` < \'123\')';
        $this->assertEquals($rString, $expectedSql);

        /*
        Like及其特例:
            [':like',       字段名, 值]             字段名 LIKE '值'
            [':begin',      字段名, 值]             字段名 LIKE '值%'
            [':end',        字段名, 值]             字段名 LIKE '%值'
            [':include',    字段名, 值]             字段名 LIKE '%值%'
            [':notlike',    字段名, 值]             字段名 NOT LIKE '值'
            [':notbegin',   字段名, 值]             字段名 NOT LIKE '值%'
            [':notend',     字段名, 值]             字段名 NOT LIKE '%值'
            [':exclude',    字段名, 值]             字段名 NOT LIKE '%值%'
        */
        $rString = \Microbe\DB\Query::buildSelect($userModel, [
            [':like', 'name', '123%_123'],
            [':begin', 'name', '123%_123'],
            [':end', 'name', '123%_123'],
            [':include', 'name', '123%_123'],
            [':notlike', 'name', '123%_123'],
            [':notbegin', 'name', '123%_123'],
            [':notend', 'name', '123%_123'],
            [':exclude', 'name', '123%_123'],
        ], FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE);
        $expectedSql = 'SELECT * FROM user WHERE';
            $expectedSql .= ' (`name` LIKE \'123%_123\')';
            $expectedSql .= ' AND (`name` LIKE \'123\\%\\_123%\')';
            $expectedSql .= ' AND (`name` LIKE \'%123\\%\\_123\')';
            $expectedSql .= ' AND (`name` LIKE \'%123\\%\\_123%\')';
            $expectedSql .= ' AND (`name` NOT LIKE \'123%_123\')';
            $expectedSql .= ' AND (`name` NOT LIKE \'123\\%\\_123%\')';
            $expectedSql .= ' AND (`name` NOT LIKE \'%123\\%\\_123\')';
            $expectedSql .= ' AND (`name` NOT LIKE \'%123\\%\\_123%\')';
        $this->assertEquals($rString, $expectedSql);

        /*
        三元操作符:
            [':between',    字段名, 值A, 值B]       字段名 BETWEEN 值A AND 值B
        集合操作符:
            [':in',         字段名, [值A, 值B]]     字段名 IN (值A, 值B)
            [':notin',      字段名, [值A, 值B]]     字段名 NOT IN (值A, 值B)
        */
        $rString = \Microbe\DB\Query::buildSelect($userModel, [
            [':between', 'age', 10, 20],
            [':in', 'name', ['Jack', 'Tom', 'Lucas', 123]],
            [':notin', 'name', ['Jack', 'Tom', 'Lucas', 123]],
            [':isnull', 'name'],
            [':notnull', 'name'],
        ], FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE);
        $expectedSql = 'SELECT * FROM user WHERE';
            $expectedSql .= ' (`age` BETWEEN 10 AND 20)';
            $expectedSql .= ' AND (`name` IN (\'Jack\', \'Tom\', \'Lucas\', \'123\'))';
            $expectedSql .= ' AND (`name` NOT IN (\'Jack\', \'Tom\', \'Lucas\', \'123\'))';
            $expectedSql .= ' AND (`name` IS NULL)';
            $expectedSql .= ' AND (`name` IS NOT NULL)';
        $this->assertEquals($rString, $expectedSql);

        /*
        逻辑操作符:
            [':not',        表达式列表]             AND NOT (表达式列表解析结果)
            [':and',        表达式列表]             AND (表达式列表解析结果)
            [':or',         表达式列表]             OR (表达式列表解析结果)
        */
        $rString = \Microbe\DB\Query::buildSelect($userModel, [
            [':gt', 'age', 30],
            ['name', ['Jack', 'Tom', 123]],
            [':or', [
                [':lt', 'age', 30],
                ['name', ['Jack', 'Tom', 123]],
            ]],
        ], FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE);
        $expectedSql = 'SELECT * FROM user WHERE';
            $expectedSql .= ' (`age` > 30)';
            $expectedSql .= ' AND (`name` IN (\'Jack\', \'Tom\', \'123\'))';
            $expectedSql .= ' OR (';
                $expectedSql .= '(`age` < 30)';
                $expectedSql .= ' AND (`name` IN (\'Jack\', \'Tom\', \'123\'))';
            $expectedSql .= ')';
        $this->assertEquals($rString, $expectedSql);

        $rString = \Microbe\DB\Query::buildSelect($userModel, [
            [':gt', 'age', 30],
            ['name', ['Jack', 'Tom', 123]],
            [':not', [
                [':lt', 'id', 10000],
                ['gender', ['male', 'famale']],
            ]],
        ], FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE);
        $expectedSql = 'SELECT * FROM user WHERE';
            $expectedSql .= ' (`age` > 30)';
            $expectedSql .= ' AND (`name` IN (\'Jack\', \'Tom\', \'123\'))';
            $expectedSql .= ' AND NOT (';
                $expectedSql .= '(`id` < 10000)';
                $expectedSql .= ' AND (`gender` IN (\'male\', \'famale\'))';
            $expectedSql .= ')';
        $this->assertEquals($rString, $expectedSql);

        /* 一个逻辑缺陷的测试 */
        $rString = \Microbe\DB\Query::buildSelect($userModel, [
            [':gt', 'age', 30],
            ['name', ['Jack', 'Tom', 123]],
            [':or', [
                [':lt', 'age', 30],
                ['name', ['Jack', 'Tom', 123]],
            ]],
            [':eq', 'id', 10000],
        ], FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE);
        $expectedSql = 'SELECT * FROM user WHERE';
            $expectedSql .= ' (`age` > 30)';
            $expectedSql .= ' AND (`name` IN (\'Jack\', \'Tom\', \'123\'))';
            $expectedSql .= ' OR (';
                $expectedSql .= '(`age` < 30)';
                $expectedSql .= ' AND (`name` IN (\'Jack\', \'Tom\', \'123\'))';
            $expectedSql .= ')';
            $expectedSql .= ' AND (`id` = 10000)';
        $this->assertEquals($rString, $expectedSql);

    }
}
class UserModel extends \Microbe\DB\Model {
    protected $tableName = 'user';
    protected $mapping = [
        'id'       => 'i',
        'name'     => 's',
        'gender'   => 's',
        'province' => 's',
        'ctime'    => 'i',
        'score'    => 'i',
    ];
}
