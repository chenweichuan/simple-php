<?php

###################################
#
# 自定义的数组的常用操作
#
########################################
class ArrayOperation
{
	/**
	 * 将二维数组中的每个数组的固定的键知道的值来形成一个新的一维数组
	 * @param $pArray 一个二维数组
	 * @param $pKey 数组的键的名称
	 * @return 返回新的一维数组
	 */
	static public function getSubByKey($pArray, $pKey, $pCondition = null){
	    $result = array();
	    foreach($pArray as $temp_array){
	        // if((null != $pCondition && $temp_array[$pCondition[0]] == $pCondition[1]) || null == $pCondition) {
	        if ( ! $pCondition ||  $temp_array[$pCondition[0]] == $pCondition[1] ) {
	            $result[] = isset($temp_array[$pKey]) ? $temp_array[$pKey] : "";
	        }
	    }
	    return $result;
	}

	/**
	 * 将二维数组中的每个数组具有唯一值的键的值来作为该二维数组的键
	 * @param $pArray 一个二维数组
	 * @param $pKey 子数组的键的名称
	 * @return array
	 */
	static public function indexBySubKey($pArray, $pKey, $pCondition = null){
	    $result = array();
	    foreach($pArray as $temp_array){
	        // if((null != $pCondition && $temp_array[$pCondition[0]] == $pCondition[1]) || null == $pCondition) {
	        if ( ! $pCondition ||  $temp_array[$pCondition[0]] == $pCondition[1] ) {
	            $result[$temp_array[$pKey]] = $temp_array;
	        }
	    }
	    return $result;
	}

	/**
	 * 通过二维数组的子数组的某一键的值来将子数组分组
	 * @param $pArray 一个二维数组
	 * @param $pKey 子数组的键的名称
	 * @return array
	 */
	static public function groupBySubKey($pArray, $pKey, $pCondition = null){
	    $result = array();
	    foreach($pArray as $temp_array){
	        // if((null != $pCondition && $temp_array[$pCondition[0]] == $pCondition[1]) || null == $pCondition) {
	        if ( ! $pCondition ||  $temp_array[$pCondition[0]] == $pCondition[1] ) {
	            $result[$temp_array[$pKey]][] = $temp_array;
	        }
	    }
	    return $result;
	}

	/**
	 +----------------------------------------------------------
	 * 对查询结果集进行排序
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param array $list 查询结果
	 * @param string $field 排序的字段名
	 * @param array $sortby 排序类型
	 * asc正向排序 desc逆向排序 nat自然排序
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	static public function sortBySubKey($list, $field, $sortby='ASC') {
	   if(is_array($list)){
	       $refer = $resultSet = array();
	       foreach ($list as $i => $data)
	           $refer[$i] = &$data[$field];
	       switch ($sortby) {
	           case 'ASC': // 正向排序
	                uasort($refer, function($a, $b){
	                	return $a > $b ? 1 : -1;
	                });
	                break;
	           case 'DESC':// 逆向排序
	                uasort($refer, function($a, $b){
	                	return $a > $b ? -1 : 1;
	                });
	                break;
	           default: // 自然排序
	                natcasesort($refer);
	       }
	       foreach ( $refer as $key=> $val)
	           $resultSet[] = &$list[$key];
	       return $resultSet;
	   }
	   return array();
	}

	/**
	 * 深度整型处理，支持以逗号分割的字符串格式，返回的是数组
	 *
	 * @param string|array $list 待处理的id 列表
	 * @return array
	 */
	static public function intvalDeep( $value )
	{
	    is_string( $value ) && ( false !== strpos( $value, ',' ) ) && $value = explode( ',', $value );
	    if (is_array($value)) {
	        $value = array_map( array( get_called_class(), 'intvalDeep' ), $value);
	    } else {
	        $value = intval($value);
	    }

	    return $value;
	}

	/**
	 * 一维整型处理，支持以逗号分割的字符串格式，返回的是数组
	 *
	 * @param string|array $list 待处理的id 列表
	 * @return array
	 */
	static public function intvalWalk( $value )
	{
	    is_array( $value ) || $value = $value ? explode( ',', $value ) : array();
	    $value = array_map( 'intval', $value);
	    return $value;
	}
}