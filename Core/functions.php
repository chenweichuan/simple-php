<?php
/* Core functions START */

/**
 * get/set config var
 *
 * @param String $key
 */
function C( $key = null, $new_value = null, $config_file = 'config.php' )
{
    static $config = array();
    array_key_exists( $config_file, $config ) || ( $config[$config_file] = include CONFIG_PATH . '/' . $config_file );
    $is_set = isset( $new_value );
    $key = isset( $key ) ? explode( '.', $key ) : array();
    $value = & $config[$config_file];
    foreach ( $key as $v ) {
        isset( $value[$v] ) || ( $is_set && $value[$v] = null );
        $value = & $value[$v];
    }
    $is_set && $value = $new_value;
    return $value;
}

/**
 * get/set rewrite rule
 *
 * @param String $key
 */
function rewrite( $router = null, $rule = null )
{
    return C( $router, $rule, 'rewrite.php' );
}

// build uri
function uri($node = null, $param = null, $hash = null)
{
    if ( $node ) {
        $node = explode('/', $node);
        ( $method = array_pop( $node ) ) || $method = 'index';
        $controller = implode('/', $node);
        $rewrite = rewrite( $controller . '/' . $method );
    }

    if ( ! $node ) {
        $uri = SITE_URI ? SITE_URI : '/';

        is_array( $param ) && $param = http_build_query( $param );
        empty( $param ) || $uri .= "?{$param}";
    } else if ( isset( $rewrite ) ) {
        $uri = $rewrite;

        if (!empty($param)) {
            is_array( $param ) || parse_str( $param, $param );
            $_remain_param = array();
            foreach ( $param as $k => $v) {
                $uri = str_replace( "[{$k}]", $v, $uri, $count );
                $count || $_remain_param[$k] = $v;
            }
            $_remain_param = array_filter( $_remain_param );
            empty( $_remain_param ) || $uri .= '?' . http_build_query( $_remain_param );
        }
    } else {
        $uri = SITE_URI . '/index.php?' . VAR_CONTROLLER . "={$controller}";
        'index' !== $method && $uri .= '&' . VAR_METHOD . "={$method}";

        is_array( $param ) && $param = http_build_query($param);
        empty( $param ) || $uri .= "&{$param}";
    }

    is_array( $hash ) && $hash = http_build_query($hash);
    empty( $hash ) || $uri .= "#{$param}";

    return $uri;
}

/* Core functions END */

// redirect to URL
function redirect($url, $time = 0, $msg = '')
{
    // 多行URL地址支持
    $url = str_replace(array('\n', '\r'), '', $url);
    
    if (empty($msg))
        $msg = "系统将在{$time}秒之后自动跳转到{$url}";
        
    if (!headers_sent()) {
        if (0 == $time) {
            header('Location: ' . $url);
        }else {
            header("refresh:{$time};url={$url}");
            
            // 防止手机浏览器下的乱码
            $meta = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            $msg = $meta . $msg;
            
            echo($msg);
        }
        exit();
    }else {
        $content = "<meta http-equiv='Refresh' content='{$time};url={$url}'>";
        if (0 != $time)
            $content .= $msg;
           
        exit($content);
    }
}

/* 处理时间的函数 START */

// 友好时间
function friendlyDate($time, $format = null)
{
    $time = intval($time);
    if (!$format) {
        $current_time = $_SERVER['REQUEST_TIME'];
        $diff_time = abs($current_time - $time);
        $diff_day = abs(date('z', $current_time) - date('z', $time));
        $diff_year = abs(date('Y', $current_time) - date('Y', $time));
        $direction = $time > $current_time ? '后' : '前';
        do {
            if (0 === $diff_time) {
                $str = '刚刚';
                break;
            }
            if ($diff_time < 60) {
                $str = $diff_time . '秒' . $direction;
                break;
            }
            if ($diff_time < 3600) {
                $str = ~~($diff_time / 60) . '分钟' . $direction;
                break;
            }
            if (0 === $diff_day && 0 === $diff_year) {
                $str = '今天' . date('H:i', $time);
                break;
            }
            if (0 === $diff_year) {
                $str = date('m月d日 H:i', $time);
                break;
            }
            // default
            $str = date('Y-m-d H:i', $time);
        } while (0);
    } else {
        $str = date($format, $time);
    }
    return $str;
}

// 友好截止时间
function friendlyDeadline( $time )
{
    $time = intval( $time );
    $current_time = $_SERVER['REQUEST_TIME'];
    if ( $time > $current_time ) {
        $diff_time = abs($time - $current_time);
        do {
            if ( $diff_time < 60 ) {
                $str = $diff_time . '秒';
                break;
            }
            if ( $diff_time < 3600 ) {
                $minutes = ~~( $diff_time / 60 );
                $seconds = $diff_time % 60;
                $str = $minutes . '分钟';
                $seconds && $str .= ( $seconds < 10 ? '0' : '' ) . $seconds . '秒';
                break;
            }
            if ( $diff_time < 86400  ) {
                $hours = ~~( $diff_time / 3600 );
                $minutes = ~~( ( $diff_time % 3600 ) / 60 );
                $str = $hours . '小时';
                $minutes && $str .= ( $minutes < 10 ? '0' : '' ) . $minutes . '分钟';
                break;
            }
            if ( $diff_time < 2592000  ) {
                $days = ~~( $diff_time / 86400 );
                $hours = ~~( ( $diff_time % 86400 ) / 3600 );
                $str = $days . '天';
                $hours && $str .= ( $hours < 10 ? '0' : '' ) . $hours . '小时';
                break;
            }
            if ( $diff_time < 31536000   ) {
                $monthes = ~~( $diff_time / 2592000 );
                $days = ~~( ( $diff_time % 2592000 ) / 86400 );
                $str = $monthes . '个月';
                $days && $str .= ( $days < 10 ? '0' : '又' ) . $days . '天';
                break;
            }
            $years = ~~( $diff_time / 31536000 );
            $monthes = ~~( ( $diff_time % 31536000 ) / 2592000 );
            $str = $years . '年';
            $monthes && $str .= ( $monthes < 10 ? '0' : '又' ) . $monthes . '个月';
        } while (0);
    } else if ( $current_time < $time ) {
        $str = '已结束';
    } else {
        $str = '刚刚结束';
    }
    return $str;
}


/* 处理时间的函数 END */

function cookie($name, $value = '', $option = null)
{
    // 默认设置
    $config = C( 'cookie' );

    // 参数设置 (覆盖黙认设置)
    if (!empty($option)) {
        if (is_numeric($option))
            $option = array('expire' => $option);
        
        $config = array_merge($config, array_change_key_case($option));
    }

    // 清除所有cookie
    if (null === $name) {
        if (empty($_COOKIE) || empty($config['prefix']))
            return FALSE;
            
        foreach ($_COOKIE as $k => $v) {
            if (0 === stripos($k, $config['prefix'])) {
                setcookie($k, '', time() - 3600, $config['path'], $config['domain']);
                unset($_COOKIE[$k]);
            }
        }
        return true;
    }
    
    // 读取or设置cookie
    $name = $config['prefix'] . $name;
    if ('' === $value) { // 读取cookie
        return $_COOKIE[$name];
    }else { // 设置cookie
        if (null === $value) {
            unset($_COOKIE[$name]);
            return setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
        }else {
            $expire = time() + intval($config['expire']);
            return setcookie($name, $value, $expire, $config['path'], $config['domain']);
        }
    }
}

/**
 * Navigates through an array and removes slashes from the values.
 *
 * If an array is passed, the array_map() function causes a callback to pass the
 * value back to the function. The slashes from this value will removed.
 *
 * @since 2.0.0
 *
 * @param array|string $value The array or string to be striped.
 * @return array|string Stripped array (or string in the callback).
 */
function stripslashes_deep($value)
{
    if (is_array($value)) {
        $value = array_map('stripslashes_deep', $value);
    } else if (is_object($value)) {
        $vars  = get_object_vars($value);
        foreach ($vars as $key => $data)
            $value->{$key} = stripslashes_deep($data);
    } else {
        $value = stripslashes($value);
    }

    return $value;
}

// xml编码
function xml_encode($data,$encoding='utf-8',$root="think") {
    $xml = '<?xml version="1.0" encoding="'.$encoding.'"?>';
    $xml.= '<'.$root.'>';
    $xml.= data_to_xml($data);
    $xml.= '</'.$root.'>';
    return $xml;
}

function data_to_xml($data) {
    if(is_object($data)) {
        $data = get_object_vars($data);
    }
    $xml = '';
    foreach($data as $key=>$val) {
        is_numeric($key) && $key="item id=\"$key\"";
        $xml.="<$key>";
        $xml.=(is_array($val)||is_object($val))?data_to_xml($val):$val;
        list($key,)=explode(' ',$key);
        $xml.="</$key>";
    }
    return $xml;
}

function isAjax()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
        || isset( $_POST['ajax'] ) && $_POST['ajax']
        || isset( $_GET['ajax'] ) && $_GET['ajax'];
}

/**
 * AJAX方式返回结果，并终止脚本
 * 
 */
function ajaxReturn($status, $info = null, $data = null, $type = 'JSON')
{
    $return = array(
        'status' => (int) $status,
        'info'   => $info,
        'data'   => $data
    );
    switch ($type) {
        case 'JSON':
            // 返回JSON数据格式到客户端 包含状态信息
            header("Content-Type:text/html; charset=utf-8");
            echo json_encode($return);
            break;
        case 'XML':
            // 返回xml格式数据
            header("Content-Type:text/xml; charset=utf-8");
            echo xml_encode($return);
            break;
        default:
            // 返回可执行的js脚本
            header("Content-Type:text/html; charset=utf-8");
            echo $data;
    }
}

function jiami($string, $key = null)
{
    if (empty($key))
        $key = C( 'secure_key' );
        
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=+";
    $rand_number = rand(0, 64);
    $rand_char   = $chars[$rand_number];
    $md5_key     = md5($key . $rand_char);
    $md5_key     = substr($md5_key, $rand_number % 8, $rand_number % 8 + 7);
    $string      = base64_encode($string);
    
    $tmp = '';
    $i   = 0;
    $j   = 0;
    $k   = 0;
    for ($i = 0; $i < strlen($string); $i++) {
        $k    = $k == strlen ($md5_key) ? 0 : $k;
        $j    = ($rand_number + strpos($chars, $string[$i]) + ord($md5_key[$k++])) % 64;
        $tmp .= $chars[$j];
    }
    return $rand_char . $tmp;
}

function jiemi($string, $key = null)
{
    if (empty($key))
        $key = C( 'secure_key' );
        
    $chars   = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=+";
    $index   = strpos($chars, $string[0]);
    $md5_key = md5($key . $string[0]);
    $md5_key = substr($md5_key, $index % 8, $index % 8 + 7);
    $string  = substr($string, 1);
    
    $tmp = '';
    $i   = 0;
    $j   = 0;
    $k   = 0;
    for ($i = 0; $i < strlen($string); $i++) {
        $k = $k == strlen($md5_key) ? 0 : $k;
        $j = strpos($chars, $string[$i]) - $index - ord($md5_key[$k++]);
        while ($j < 0)
            $j += 64;
            
        $tmp .= $chars [$j];
    }
    
    return base64_decode($tmp);
}

/* 处理字符的函数 START */

/**
 * HTML 安全过滤
 * 
 * @param string $text 待转换的字符串
 * @param string $tags 白名单, 即白名单内的标签不被过滤 (使用“|”分割)
 */
function h($text, $tags = null){
    $text   =   trim($text);
    $text   =   preg_replace('/<!--?.*-->/','',$text);
    //完全过滤注释
    $text   =   preg_replace('/<!--?.*-->/','',$text);
    //完全过滤动态代码
    $text   =   preg_replace('/<\?|\?'.'>/','',$text);
    //完全过滤js
    $text   =   preg_replace('/<script?.*\/script>/','',$text);

    $text   =   str_replace('[','&#091;',$text);
    $text   =   str_replace(']','&#093;',$text);
    $text   =   str_replace('|','&#124;',$text);
    //过滤换行符
    $text   =   preg_replace('/\r?\n/','',$text);
    //br
    $text   =   preg_replace('/<br(\s\/)?'.'>/i','[br]',$text);
    $text   =   preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
    //过滤危险的属性，如：过滤on事件lang js
    while(preg_match('/(<[^><]+) (lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
        $text=str_replace($mat[0],$mat[1],$text);
    }
    while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
        $text=str_replace($mat[0],$mat[1].$mat[3],$text);
    }
    if(empty($tags)) {
        $tags = 'table|tbody|td|th|tr|i|b|u|strong|img|p|br|div|span|em|ul|ol|li|dl|dd|dt|a|alt|h[1-9]?';
        $tags.= '|object|param|embed';  // 音乐和视频
    }
    //允许的HTML标签
    $text   =   preg_replace('/<(\/?(?:'.$tags.'))( [^><\[\]]*)?>/i','[\1\2]',$text);
    //过滤多余html
    $text   =   preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|style|xml)[^><]*>/i','',$text);
    //过滤合法的html标签
    while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i',$text,$mat)){
        $text=str_replace($mat[0],str_replace('>',']',str_replace('<','[',$mat[0])),$text);
    }
    //转换引号
    while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2\[\]]*)\2([^\[\]]*\])/i',$text,$mat)){
        $text = str_replace($mat[0], $mat[1] . '|' . $mat[3] . '|' . $mat[4],$text);
    }
    //转换其它所有不合法的 < >
    $text   =   str_replace('<','&lt;',$text);
    $text   =   str_replace('>','&gt;',$text);
    // $text   =   str_replace('"','&quot;',$text);
    //$text   =   str_replace('\'','&#039;',$text);
     //反转换
    $text   =   str_replace('[','<',$text);
    $text   =   str_replace(']','>',$text);
    $text   =   str_replace('|','"',$text);
    //过滤多余空格
    $text   =   str_replace('  ',' ',$text);
    return $text;
}

/**
 * 输出安全的纯文本
 * 
 * @param string  $text
 * @param boolean $parse_br    是否转换换行符和空格
 * @param int     $quote_style ENT_QUOTES(默认):过滤单引号和双引号 ENT_NOQUOTES:不过滤单引号和双引号 ENT_COMPAT:过滤双引号,而不过滤单引号
 * @return string string:被转换的字符串
 */
function t($text, $parse = false, $quote_style = ENT_QUOTES)
{
    if (is_numeric($text))
        $text = (string)$text;
    
    if (!is_string($text))
        return '';

    $text = stripslashes($text);
    $text = htmlspecialchars($text, $quote_style, 'UTF-8');

    if (!$parse) {
        $text = preg_replace('/^\s+|\s+$/', '', $text);
        $text = str_replace(array("\r","\n","\t"), ' ', $text);
    } else {
        $text = str_ireplace(' ', '&nbsp;', $text);
        $text = str_replace("\n", '<br />', $text);
        $text = str_replace(array("\r","\t"), '', $text);
    }

    return $text;
}

/**
 * 将安全的纯文本反转为原始输入，被过滤掉的\r,\n,\t和空格无法还原
 * 
 * @param string  $text
 * @param boolean $parse_br    是否已转换换行符和空格
 * @param int     $quote_style ENT_QUOTES(默认):过滤单引号和双引号 ENT_NOQUOTES:不过滤单引号和双引号 ENT_COMPAT:过滤双引号,而不过滤单引号
 * @return string string:被转换的字符串
 */
function unt($text, $parse = false, $quote_style = ENT_QUOTES)
{
    if (is_numeric($text))
        $text = (string)$text;

    if (!is_string($text))
        return '';

    if ($parse) {
        $text = preg_replace('/\<br\s*\/?\>/', "\n", $text);
        $text = str_ireplace('&nbsp;', ' ', $text);
    }
    $text = html_entity_decode($text, $quote_style, 'UTF-8');

    return $text;
}

/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其它编码
 +----------------------------------------------------------
 * @static
 * @access public
 +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $suffix 截断显示字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function getSubStr( $str, $start, $length, $suffix = '...' )
{
    $str = htmlspecialchars_decode($str, ENT_QUOTES);
    $str = strip_tags($str);

    // 字符串完整的长度
    $str_full_len = (strlen($str) + mb_strlen($str, 'UTF8')) / 4;
    // 若字符串长度在范围内，取消后缀
    $str_full_len <= $length && $suffix = '';
    // 若字符串长度超出范围，则加上对suffix 长度的考虑，修正实际需要截取的长度
    $str_full_len > $length && $length = $length - ~~ ( (strlen($suffix) + mb_strlen($suffix, 'UTF8')) / 4 );
    // 截取处理
    $start *= 2;
    $length *= 2;
    $str = preg_replace('/[\xe0-\xef][\x80-\xbf]{2}/', '${0} ', $str);
    $start > 0 && $str = preg_replace("/^.{{$start}} ?/u", '', $str);
    $str = preg_replace("/(?<=.{{$length}}).+/u", $suffix, $str);
    $str = preg_replace('/([\xe0-\xef][\x80-\xbf]{2}) /', '${1}', $str);
    return $str;
}

/**
 * 获取字符串的长度
 *
 * 计算时, 汉字或全角字符占1个长度, 英文字符占0.5个长度
 *
 * @param string  $str
 * @param boolean $filter 是否过滤html标签
 * @return int 字符串的长度
 */
function getStrLength($str, $filter = false)
{
    if ($filter) {
        $str = html_entity_decode($str, ENT_QUOTES);
        $str = strip_tags($str);
    }
    return (strlen($str) + mb_strlen($str, 'UTF8')) / 4;
}

/* 处理字符的函数 END */

/*
 * 图片切割
 *
 */
function thumbnail($file, $width=100, $height='auto', $cut=true, $sharpen = false)
{
    ! intval( $width ) && $width = 'auto';
    ! intval( $height ) && $height = 'auto';

    $originalPath = realpath(UPLOAD_PATH . '/' . $file);
    if (!getimagesize($originalPath)) {
        return false;
    }

    $info = pathinfo($originalPath);
    $thumbnailPath = str_ireplace(UPLOAD_PATH, CACHE_PATH . '/thumbnail', $info['dirname'])
               . DIRECTORY_SEPARATOR . $info['filename']
               . '_' . $width . 'x' . $height . '_' . intval($cut) . '_' . intval($sharpen) . '.' . $info['extension'];
    $thumbnailPath = str_replace('\\', '/', $thumbnailPath);
    if(filemtime($originalPath) > filemtime($thumbnailPath)){
        // 检测缩略图目录
        $thumbnailDir = dirname($thumbnailPath);
        if (!file_exists($thumbnailDir)) {
            mkdir($thumbnailDir, 0777, true);
        }
        //生成缩略图
        if($cut){
            Image::cut($originalPath, $thumbnailPath,  $width, $height, $sharpen);
        }else{
            Image::thumb($originalPath, $thumbnailPath , '' , $width , $height, $sharpen);   
        }

        if(!file_exists($thumbnailPath)){
            $thumbnailPath = $originalPath;
        }
    }
    return str_replace(PUBLIC_PATH, uri(), $thumbnailPath);
}

// 数据友好的打印，支持cli 和cgi
function dump($var, $echo = true, $label = null, $strict = true)
{
    // $var = & _dumpHelper( $var );
    $label = ($label === null) ? '' : rtrim($label) . ' ';

    if(!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre style="text-align:left">'.$label.htmlspecialchars($output,ENT_QUOTES).'</pre>';
        } else {
            $output = $label . " : " . print_r($var, true);
        }
    }else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        $output = preg_replace("/ =>\n/", " =>", $output);
        if( 'cli' !== PHP_SAPI ) {
            $output = '<pre style="text-align:left">'. $label. htmlspecialchars($output, ENT_QUOTES). '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else {
        return $output;
    }
}




