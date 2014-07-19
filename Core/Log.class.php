<?php
class Core_Log extends Core_Core
{
	private $_log_dir = null;

	private $_logs = array();

	const LEVEL_INFO  = 1;
	const LEVEL_WARN  = 2;
	const LEVEL_ERROR = 3;

	static private $_level = array(
		self::LEVEL_INFO  => 'INFO',
		self::LEVEL_WARN  => 'WARN',
		self::LEVEL_ERROR => 'ERROR',
	);

	public function __construct()
	{
		$this->_log_dir = APPLICATION_PATH . '/log';
		is_dir( $this->_log_dir ) || mkdir( $this->_log_dir, 0777, true );
	}

	public function log( $str, $level_code = 1, $file_prefix = '' )
	{
		$date = date( 'Y-m-d/H:i:s' );
		$level = self::$_level[$level_code];
		$message = "[{$date}] [{$level}] [{$str}]\n\n";
		$file = $this->_log_dir . '/' . $file_prefix . date( 'Y-m-d' ) . '.log';
		$this->_logs[] = $message;
		file_put_contents( $file, $message, FILE_APPEND );
		IS_CLI && chmod( $file, 0777 );
	}

	public function info( $str, $file_prefix = 'info_' )
	{
		$this->log( $str, self::LEVEL_INFO, $file_prefix );
	}

	public function warn( $str, $file_prefix = 'warn_' )
	{
		$this->log( $str, self::LEVEL_WARN, $file_prefix );
	}

	public function error( $str, $file_prefix = 'error_' )
	{
		$this->log( $str, self::LEVEL_ERROR, $file_prefix );
	}

	public function showLogs()
	{
        array_map( 'print_r', $this->_logs );
        $this->_logs = array();
	}
}