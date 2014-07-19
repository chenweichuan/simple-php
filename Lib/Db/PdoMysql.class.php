<?php
/**
 * PDO Mysql服务
 * 
 * 支持主从分离
 *
 * @author 灬慢节奏 <258396027@qq.com>
 * @version $Id$
 *
 */
 
/**
 * Pdo Mysql Class
 *
 * <code>
 * <?php
 * $mysql = new PdoMysql();
 *
 * $sql = "SELECT * FROM `user` LIMIT 10";
 * $data = $mysql->getData( $sql );
 * $name = strip_tags( $_REQUEST['name'] );
 * $age = intval( $_REQUEST['age'] );
 * $sql = "INSERT  INTO `user` ( `name` , `age` , `regtime` ) VALUES ( '"  . $mysql->escape( $name ) . "' , '" . intval( $age ) . "' , NOW() ) ";
 * $mysql->runSql( $sql );
 * if( $mysql->errno() != 0 )
 * {
 *     die( "Error:" . $mysql->errmsg() );
 * }
 * 
 * $mysql->closeDb();
 * ?>
 * </code>
 *
 * @author 灬慢节奏
 * 
 */ 
class PdoMysql 
{
    private $port;
    private $r_host;
    private $w_host;

    private $accesskey;
    private $secretkey;
    private $appname;

    private $charset;

    private $do_replication;

    private $db_read;
    private $db_write;

    /**
     * 构造函数
     *    
     * @param bool $do_replication 是否支持主从分离，true:支持，false:不支持，默认为true
     * @return void 
     * @author EasyChen
     */
    function __construct( $do_replication = false )
    {
        $this->do_replication = $do_replication;
    }
 
    /**
     * 设置keys
     *    
     * 当需要连接其他APP的数据库时使用
     * 
     * @param string $akey AccessKey
     * @param string $skey SecretKey
     * @return void 
     * @author EasyChen
     */
    public function setAuth( $akey , $skey )
    {
        $this->accesskey = $akey;
        $this->secretkey = $skey;
    }
 
    /**
     * 设置Mysql服务器端口
     *
     * 当需要连接其他APP的数据库时使用
     * 
     * @param string $port 
     * @return void 
     * @author EasyChen
     */
    public function setPort( $port )
    {
        $this->port = $port;
    }

    /**
     * 设置Mysql服务器地址
     *
     * 当需要连接其他APP的数据库时使用
     * 
     * @param string $port 
     * @return void 
     * @author EasyChen
     */
    public function setHost( $r_host, $w_host )
    {
        $this->r_host = $r_host;
        $this->w_host = $w_host;
    } 
 
    /**
     * 设置Appname
     *
     * 当需要连接其他APP的数据库时使用
     *
     * @param string $appname 
     * @return void 
     * @author EasyChen
     */
    public function setAppname( $appname )
    {
        $this->appname = $appname;
    }
 
 
    /**
     * 设置当前连接的字符集 , 必须在发起连接之前进行设置
     *
     * @param string $charset 字符集,如GBK,GB2312,UTF8
     * @return void 
     */
    public function setCharset( $charset )
    {
        $this->charset = $charset;
    }
 
    /**
     * 运行Sql语句,返回影响的行数
     *
     * @param string $sql 
     * @return pdo_result|bool
     */
    public function runSql( $sql )
    {
        $this->last_sql = $sql;
        $dblink = $this->_dbWrite();
        if ($dblink === false) {
            return false;
        }
        $ret = $this->affected_rows = $dblink->exec( $sql );
        $this->_saveError( $dblink );
        return $ret;
    }
 
    /**
     * 运行Sql,以多维数组方式返回结果集
     *
     * @param string $sql 
     * @return array 成功返回数组，失败时返回false
     */
    public function getData( $sql )
    {
        $this->last_sql = $sql;
        $data = array();
        $i = 0;
        $dblink = $this->do_replication ? $this->_dbRead() : $this->_dbWrite();
        if ($dblink === false) {
            return false;
        }
        $stmt = $dblink->prepare( $sql );
        $stmt->execute();
        $this->affected_rows = $stmt->rowCount();

        $this->_saveError( $stmt );
 
        while( $Array = $stmt->fetch( PDO::FETCH_ASSOC ) )
        {
            $data[$i++] = $Array;
        }
 
        $stmt->closeCursor();
 
        if( count( $data ) > 0 )
            return $data;
        else
            return null;   
    }
 
    /**
     * 运行Sql,以数组方式返回结果集第一条记录
     *
     * @param string $sql 
     * @return array 成功返回数组，失败时返回false
     */
    public function getLine( $sql )
    {
        $data = $this->getData( $sql );
        if ($data) {
            return reset($data);
        } else {
            return false;
        }
    }
 
    /**
     * 运行Sql,返回结果集第一条记录的第一个字段值
     *
     * @param string $sql 
     * @return mixxed 成功时返回一个值，失败时返回false
     */
    public function getVar( $sql )
    {
        $data = $this->getLine( $sql );
        if ($data) {
            return $data[ reset(array_keys( $data )) ];
        } else {
            return false;
        }
    }
 
    /**
     * 同mysqli_affected_rows函数
     *
     * @return int 成功返回行数,失败时返回-1
     */
    public function affectedRows()
    {
        return $this->affected_rows;
    }
 
    /**
     * 同mysqli_insert_id函数
     *
     * @return int 成功返回last_id,失败时返回false
     */
    public function lastId()
    {
        $result = $this->_dbWrite()->lastInsertId();
        return $result;
    }
 
    /**
     * 关闭数据库连接
     *
     * @return bool 
     */
    public function closeDb()
    {
        $this->db_read = null;
        $this->db_write = null;
    }
 
    /**
     *  同mysqli_real_escape_string
     *
     * @param string $str 
     * @return string 
     * @author EasyChen
     */
    public function escape( $str )
    {
        if ( isset($this->db_read) ) {
            $db = $this->db_read;
        } else if ( isset($this->db_write) ) {
            $db = $this->db_write;
        } else {
            $db = $this->_dbRead();
        }
        $str = $db->quote( $str );
        return substr( $str, 1, -1 );
    }
 
    /**
     * 返回错误码
     * 
     *
     * @return int 
     */
    public function errno()
    {
        return $this->errno;
    }
 
    /**
     * 返回错误信息
     *
     * @return string 
     */
    public function error()
    {
        return $this->error;
    }
 
    /**
     * 返回错误信息,error的别名
     *
     * @return string 
     */
    public function errmsg()
    {
        return $this->error();
    }
 
    /**
     * 返回最后一次执行的SQL
     *
     * @return string 
     */
    public function lastSql()
    {
        return $this->last_sql;
    }
 
    /**
     * @ignore
     */
    private function _connect( $is_master = true )
    {
        if ($this->port == 0) {
            $this->error = 13048;
            $this->errno = 'Not Initialized';
            return false;
        }
        if( $is_master ) $host = $this->w_host;
        else $host = $this->r_host;
 
        try {
            $db = new PDO(
                "mysql:host={$host};port={$this->port};dbname={$this->appname};charset={$this->charset}",
                $this->accesskey,
                $this->secretkey
            );
            $db->setAttribute( PDO::ATTR_TIMEOUT, 5 );
        } catch ( PDOException $e ) {
            $this->error = $e->getMessage();
            $this->errno = $e->getCode();
            return false;
        }

        return $db;
    }
 
    /**
     * @ignore
     */
    private function _dbRead()
    {
        if( isset( $this->db_read ) )
        {
            return $this->db_read;
        }
        else
        {
            if( !$this->do_replication ) return $this->_dbWrite();
            else
            {
                $this->db_read = $this->_connect( false );
                return $this->db_read;
            }
        }
    }
 
    /**
     * @ignore
     */
    private function _dbWrite()
    {
        if( isset( $this->db_write ) )
        {
            return $this->db_write;
        }
        else
        {
            $this->db_write = $this->_connect( true );
            return $this->db_write;
        }
    }
 
    /**
     * @ignore
     */
    private function _saveError($db_or_stmt)
    {
        $error_info = $db_or_stmt->errorInfo();
        $this->error = $error_info[2];
        $this->errno = PDO::ERR_NONE === $error_info[0] ? 0 : $error_info[1];
    }
 
    private $error;
    private $errno;
    private $last_sql;
    private $affected_rows;
}