<?php
class Core_Model extends Core_Core
{
    // MySQL实例
    static private $_mysql   = null;
    // 表名
    protected $table_name    = '';
    // 查询表达式的参数
    protected $options       =   array();

    public function __construct()
    {
    }

    static public function connectDb()
    {
        if (!self::$_mysql) {
            $db_config = C( 'db' );
            self::$_mysql = new PdoMysql();
            self::$_mysql->setAuth( $db_config['username'], $db_config['password'] );
            // [ read / write ] host
            self::$_mysql->setHost( $db_config['r_host'], $db_config['w_host'] );
            self::$_mysql->setPort( $db_config['port'] );
            self::$_mysql->setAppname( $db_config['dbname'] );
            self::$_mysql->setCharset( $db_config['charset'] );
        }
    }

    static public function closeDb()
    {
        if ( self::$_mysql ) {
            self::$_mysql->closeDb();
        }
    }

    static public function getDb()
    {
        return self::$_mysql;
    }

    /**
     * 在数据表中新增一行数据
     *
     * @param array $row 一维数组形式，每个元素键是数据表中的字段名，键对应的值是需要新增的数据。
     */
    public function insert( $row )
    {
        if( false !== $this->multiInsert( array( $row ) ) ) { // 获取当前新增的ID
            if( $last_id = self::$_mysql->lastId() ){
                return $last_id;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * 在数据表中新增多行数据
     *
     * @param array $rows 二维数组形式，每个元素数组的键是数据表中的字段名，键对应的值是需要新增的数据。
     */
    public function multiInsert( $rows )
    {
        $options = $this->_parseOptions();
        if ( ! is_array($rows) || empty( $rows ) ) return false;
        $vals = array();
        foreach($rows as $key => $row){
            !$cols && $cols = array_keys($row);
            foreach ($cols as $col) {
                $row[$col] = self::escape( $row[$col] );
                is_string( $row[$col] ) && $row[$col] = "'{$row[$col]}'";
                $vals[$key][$col] = $row[$col];
            }
            $vals[$key] = '(' . join(',', $vals[$key]) . ')';
        }

        $cols = '`' . join('`,`', $cols) . '`';
        $vals = join(',', $vals);

        $sql = "INSERT INTO {$options['table']} ({$cols}) VALUES {$vals}";
        $result = $this->execute($sql);
        return $result;
    }

    public function delete()
    {
        $options = $this->_parseOptions();
        $sql = "DELETE FROM {$options['table']}";
        $options['where'] && $sql .= " WHERE {$options['where']}";
        $options['limit'] && $sql .= " LIMIT {$options['limit']}";
        return $this->execute($sql);
    }

    public function update( $row )
    {
        $options = $this->_parseOptions();
        if(empty($row))return false;
        if(is_array($row)){
            $vals = array();
            foreach($row as $key => $value){
                $value = self::escape($value);
                // 值为字符串类型，则加上单引号
                is_string( $value ) && $value = "'{$value}'";
                $vals[] = "`{$key}` = {$value}";
            }
            $vals = join(", ",$vals);
        } else {
            $vals = $row;
        }

        $sql = "UPDATE {$options['table']} SET {$vals}";
        $options['where'] && $sql .= " WHERE {$options['where']}";
        $options['limit'] && $sql .= " LIMIT {$options['limit']}";
        return $this->execute($sql);
    }

    public function select()
    {
        $options = $this->_parseOptions();
        $sql = "SELECT {$options['field']} FROM {$options['table']}";
        $options['where'] && $sql .= " WHERE {$options['where']}";
        $options['group'] && $sql .= " GROUP BY {$options['group']}";
        $options['order'] && $sql .= " ORDER BY {$options['order']}";
        $options['limit'] && $sql .= " LIMIT {$options['limit']}";
        return $this->query($sql);
    }

    // 获取一行
    public function selectLine()
    {
        $this->limit( 1 );
        $data = $this->select();
        if ( false !== $data ) {
            return reset($data);
        } else {
            return false;
        }
    }

    /**
     * 计算符合条件的记录数量
     *
     */
    public function count()
    {
        $options = $this->_parseOptions();
        preg_match( '/(COUNT\([^\(\)]+\))(?:\s+AS\s+`?([^\s`]+)`?)?/i', $options['field'], $match );
        if ( ! empty( $match[1] ) ) {
            $count_field = $match[2] ? $match[2] : $match[1];
        } else {
            $options['field'] = 'COUNT(*) AS `count`';
            $count_field = 'count';
        }
        $this->field( $options['field'] )->where( $options['where'] )->group( $options['group'] );
        $result = $this->selectLine();
        return $result[$count_field];
    }

    /**
     * 获取翻页信息
     *
     */
    public function page( $page_size = null, $count = null, $page = null )
    {
        ! is_numeric( $page_size ) && $page_size = 20;
        if ( ! is_numeric( $count ) ) {
            // 不涉及order 故删除order
            unset( $this->options['order'] );
            $count = $this->count();
        }

        $p = new Page( $count, $page_size, VAR_PAGE, $page );
        // 输出
        $output               = array();
        $output['firstRow']   = $p->firstRow;
        $output['listRows']   = $p->listRows;
        $output['limit']      = $p->firstRow . ',' . $p->listRows;
        $output['totalRows']  = $p->totalRows;
        $output['nowPage']    = $p->nowPage;
        $output['totalPages'] = $p->totalPages;
        unset($p);
        // 输出数据
        return $output;
    }

    public function field( $field )
    {
        is_array( $field ) && $field = '`' . implode( '`,`', $field ) . '`';
        $this->options['field'] = $field;
        return $this;
    }

    public function table($table)
    {
        $this->options['table'] = "{$table}";
        return $this;
    }

    public function where( $where )
    {
        if( is_array( $where ) ){
            $join = array();
            foreach ( $where as $key => $value ) {
                // key 为数字，说明该元素没有传递字段，则直接作为一个AND 的条件
                if ( is_numeric( $key ) ) {
                    $join[] = "({$value})";
                } else if ( is_array( $value ) ) {
                    if ( in_array( $value[0], array( 'IN', 'NOT IN' ) ) ) {
                        if ( is_array( $value[1] ) ) {
                            $value[1] = array_unique( $value[1] );
                            foreach ( $value[1] as $k => $v ) {
                                $v = self::escape( $v );
                                // 值为字符串类型，则加上单引号
                                is_string( $v ) && $v = "'{$v}'";
                                $value[1][$k] = $v;
                            }
                            $value[1] = join( ',', $value[1] );
                        }
                        $join[] = "`{$key}` {$value[0]} ({$value[1]}) ";
                    } else if ( in_array( $value[0], array( 'LIKE', 'NOT LIKE' ) ) ) {
                        $value[1] = "'{$value[1]}'";
                        $join[] = "`{$key}` {$value[0]} {$value[1]} ";
                    }
                } else {
                    $value = self::escape( $value );
                    // 值为字符串类型，则加上单引号
                    is_string( $value ) && $value = "'{$value}'";
                    $join[] = "`{$key}` = {$value}";
                }
            }
            !empty($join) && $where = join(" AND ",$join);
        }
        $this->options['where'] = $where;
        return $this;
    }

    public function group($group)
    {
        $this->options['group'] = $group;
        return $this;
    }

    public function order($order)
    {
        $this->options['order'] = $order;
        return $this;
    }

    public function limit($limit)
    {
        $this->options['limit'] = $limit;
        return $this;
    }

    /**
     * 运行Sql,以多维数组方式返回结果集，此方法无视查询参数
     *
     * @param string $sql 
     * @return array 成功返回数组，失败时返回false
     * @author EasyChen
     */
    public function query( $sql )
    {
        $data = self::$_mysql->getData( $sql );
        if( self::$_mysql->errno() != 0 ) {
            $this->errmsg = self::$_mysql->errmsg();
            $data = false;
        }
        return $data;
    }

    /**
     * 运行Sql语句,不返回结果集，此方法无视查询参数
     *
     * @param string $sql 
     * @return mysqli_result|bool
     */
    public function execute( $sql )
    {
        $result = self::$_mysql->runSql( $sql );
        if( self::$_mysql->errno() != 0 ) {
            $this->errmsg = self::$_mysql->errmsg();
            $result = false;
        } else {
            $result = self::$_mysql->affectedRows();
        }
        return $result;
    }

    /**
     * 转义字符串的特殊字符, 以保证SQL 语句安全
     * 
     * 如果传入的参数为数组, 则遍历数组的所有元素, 并对每个元素使用escape方法
     * 
     * @param string|array $value
     * @return 转义后的数值
     */
    static public function escape($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::escape($v);
            }
        } else if (is_string($value)) {
            $value = self::$_mysql->escape($value);
        } else if ( is_numeric( $value ) ) {
            $value = intval( $value );
        } else {
            $value = '';
        }

        return $value;
    }
 
    /**
     * 返回最后一次执行的SQL
     *
     * @return string 
     * @author EasyChen
     */
    static public function getLastSql()
    {
        return self::$_mysql->lastSql();
    }

    private function _parseOptions()
    {
        // 补足必要参数
        empty( $this->options['field'] ) && $this->field( '*' );
        empty( $this->options['table'] ) && $this->table( $this->table_name );

        // 未定义参数进行声明
        foreach ( array( 'where', 'group', 'order', 'limit' ) as $v ) {
            isset( $this->options[$v] ) || $this->options[$v] = null;
        }

        $options = $this->options;
        // 清除，避免影响下一次查询
        $this->options = array();

        return $options;
    }
}