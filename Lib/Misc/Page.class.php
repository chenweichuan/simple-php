<?php
// +----------------------------------------------------------------------
// | ThinkPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2008 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * 分页显示类
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class Page
{//类定义开始

    /**
     +----------------------------------------------------------
     * 分页起始行数
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    public $firstRow ;

    /**
     +----------------------------------------------------------
     * 列表每页显示行数
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    public $listRows ;

    /**
     +----------------------------------------------------------
     * 分页总页面数
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    public $totalPages  ;

    /**
     +----------------------------------------------------------
     * 总行数
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    public $totalRows  ;

    /**
     +----------------------------------------------------------
     * 当前页数
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    public $nowPage    ;

    /**
     +----------------------------------------------------------
     * 分页的栏的总页数
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    public $coolPages   ;

    /**
     +----------------------------------------------------------
     * 分页栏每页显示的页数
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    public $rollPage   ;

    /**
     +----------------------------------------------------------
     * 分页记录名称
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    public $varPage   ;

    // 分页显示定制
    public $config = array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页');

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param int $totalRows  总的记录数
     * @param int $listRows  每页显示记录数
     * @param string $varPage  分页跳转的参数
     * @param int $nowPage 当前页码
     +----------------------------------------------------------
     */
    public function __construct($totalRows,$listRows='',$varPage='', $nowPage = null)
    {
        $this->totalRows = $totalRows;
        $this->rollPage = 5;
        $this->listRows = !empty($listRows)?$listRows:20;
        $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->varPage = strip_tags( $varPage );

        $nowPage = isset( $nowPage ) ? $nowPage : ( isset( $_REQUEST[$this->varPage] ) ? $_REQUEST[$this->varPage] : 1 );
        if( (!empty($this->totalPages) && $nowPage>$this->totalPages) || $nowPage=='last' ) {
            $this->nowPage = $this->totalPages;
        }else{
            $this->nowPage  = $nowPage > 0 ? intval( $nowPage ) : 1;
        }

        $this->firstRow = $this->listRows*($this->nowPage-1);

    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     +----------------------------------------------------------
     * 分页显示
     * 用于在页面显示的分页栏的输出
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function show($isArray=false)
    {
        if(0 == $this->totalRows) return;

        $url    =   preg_replace('/(#.+$|' . $this->varPage . '=[0-9]+)/i','',$_SERVER['REQUEST_URI']);
        $url    =   $url.(strpos($url,'?')?'':"?");
        $url    =   preg_replace('/(&+)/','&',$url);

        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage="<a href='".$url."&".$this->varPage."=$upRow'>".$this->config['prev']."</a>";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
            $downPage="<a href='".$url."&".$this->varPage."=$downRow'>".$this->config['next']."</a>";
        }else{
            $downPage="";
        }

        // 1 2 [3] 4 5
        $linkPage = "";
        //dump(ceil($this->rollPage/2)-1);
        $halfRoll   =   ceil($this->rollPage/2);

        if( $this->totalPages <= $this->rollPage ){
            $leftPages  =   $this->nowPage-1;
            $rightPages =   $this->totalPages-$leftPages-1;
        }elseif( ($this->nowPage < $halfRoll) && ($this->totalPages > $this->rollPage) ){
            $leftPages  =   $this->nowPage-1;
            $rightPages =   $this->rollPage-$leftPages-1;
        }elseif( ($this->totalPages-$this->nowPage) < $halfRoll ){
            $rightPages =   $this->totalPages-$this->nowPage;
            $leftPages  =   $this->rollPage-$rightPages-1;
        }else{
            $rightPages =   $this->rollPage-$halfRoll;
            $leftPages  =   $this->rollPage-$rightPages-1;
        }

        if($leftPages>0){
            for($i=$this->nowPage-$leftPages;$i<$this->nowPage;$i++){
                $linkPage .= "<a href='".$url."&".$this->varPage."=$i'>".$i."</a>";
            }
        }
        $linkPage .= " <span class='current'>".$this->nowPage."</span>";
        if($rightPages>0){
            for($i=$this->nowPage+1;$i<=$this->nowPage+$rightPages;$i++){
                $linkPage .= "<a href='".$url."&".$this->varPage."=$i'>".$i."</a>";
            }
        }
        // << < > >>
        if( $this->nowPage <= $halfRoll || $this->totalPages <= $this->rollPage ){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<a href='".$url."&".$this->varPage."=$preRow' >上".$this->rollPage."页</a>";
            $theFirst = "<a href='".$url."&".$this->varPage."=1' >1..</a>";
        }

        if( ($this->totalPages-$this->nowPage) < $halfRoll || $this->totalPages <= $this->rollPage ){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "<a href='".$url."&".$this->varPage."=$nextRow' >下".$this->rollPage."页</a>";
            $theEnd = "<a href='".$url."&".$this->varPage."=$theEndRow' >..{$theEndRow}</a>";
        }

        if( ( $this->totalPages+1 - $halfRoll ) == $this->nowPage || $this->totalPages == $this->nowPage ){
            $theEnd = "";
        }

        $pageStr = null;
        if( $this->totalPages > 1 )
            $pageStr = $upPage.$theFirst.$linkPage.$theEnd.$downPage;
        if($isArray) {
            $pageArray['totalRows'] =   $this->totalRows;
            $pageArray['upPage']    =   $url.'&'.$this->varPage."=$upRow";
            $pageArray['downPage']  =   $url.'&'.$this->varPage."=$downRow";
            $pageArray['totalPages']=   $this->totalPages;
            $pageArray['firstPage'] =   $url.'&'.$this->varPage."=1";
            $pageArray['endPage']   =   $url.'&'.$this->varPage."=$theEndRow";
            $pageArray['nextPages'] =   $url.'&'.$this->varPage."=$nextRow";
            $pageArray['prePages']  =   $url.'&'.$this->varPage."=$preRow";
            $pageArray['linkPages'] =   $linkPage;
            $pageArray['nowPage'] =   $this->nowPage;
            return $pageArray;
        }
        return $pageStr;
    }

    public function wapShow($isArray=false){

        if(0 == $this->totalRows) return;

        $url    =   eregi_replace("(#.+$|p=[0-9]+)",'',$_SERVER['REQUEST_URI']);
        $url    =   $url.(strpos($url,'?')?'':"?");
        $url    =   eregi_replace("(&+)",'&',$url);

        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage="<a href='".$url."&".$this->varPage."=$upRow'>".$this->config['prev']."</a>";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
            $downPage="<a href='".$url."&".$this->varPage."=$downRow'>".$this->config['next']."</a>";
        }else{
            $downPage="";
        }

        $linkPage = "";
        $halfRoll   =   ceil($this->rollPage/2);

        if( $this->totalPages <= $this->rollPage ){
            $leftPages  =   $this->nowPage-1;
            $rightPages =   $this->totalPages-$leftPages-1;
        }elseif( ($this->nowPage < $halfRoll) && ($this->totalPages > $this->rollPage) ){
            $leftPages  =   $this->nowPage-1;
            $rightPages =   $this->rollPage-$leftPages-1;
        }elseif( ($this->totalPages-$this->nowPage) < $halfRoll ){
            $rightPages =   $this->totalPages-$this->nowPage;
            $leftPages  =   $this->rollPage-$rightPages-1;
        }else{
            $rightPages =   $this->rollPage-$halfRoll;
            $leftPages  =   $this->rollPage-$rightPages-1;
        }

        if($leftPages>0){
            for($i=$this->nowPage-$leftPages;$i<$this->nowPage;$i++){
                $linkPage .= "<a href='".$url."&".$this->varPage."=$i'>".$i."</a>";
            }
        }
        $linkPage .= " <span class='current'>".$this->nowPage."</span>";
        if($rightPages>0){
            for($i=$this->nowPage+1;$i<=$this->nowPage+$rightPages;$i++){
                $linkPage .= "<a href='".$url."&".$this->varPage."=$i'>".$i."</a>";
            }
        }
        // << < > >>
        if( $this->nowPage <= $halfRoll || $this->totalPages <= $this->rollPage ){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<a href='".$url."&".$this->varPage."=$preRow' >上".$this->rollPage."页</a>";
            $theFirst = "<a href='".$url."&".$this->varPage."=1' >1..</a>";
        }

        if( ($this->totalPages-$this->nowPage) < $halfRoll || $this->totalPages <= $this->rollPage ){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "<a href='".$url."&".$this->varPage."=$nextRow' >下".$this->rollPage."页</a>";
            $theEnd = "<a href='".$url."&".$this->varPage."=$theEndRow' >..{$theEndRow}</a>";
        }

        if( ( $this->totalPages+1 - $halfRoll ) == $this->nowPage || $this->totalPages == $this->nowPage ){
            $theEnd = "";
        }

        if( $this->totalPages > 1 ){
            $pageStr = '<form method="post" action="'.$url.'"><span>'.$upPage.'&nbsp;'.$downPage.'&nbsp;'.$this->nowPage.'/'.$this->totalPages.'页</span>';
            $pageStr .= '<input type="text" style="margin-left:8px;width:40px" name="'.$this->varPage.'" value="'.$this->nowPage.'" />';
            $pageStr .= '<input type="submit" value="转至" /></form>';
        }

        if($isArray) {
            $pageArray['totalRows'] =   $this->totalRows;
            $pageArray['upPage']    =   $url.'&'.$this->varPage."=$upRow";
            $pageArray['downPage']  =   $url.'&'.$this->varPage."=$downRow";
            $pageArray['totalPages']=   $this->totalPages;
            $pageArray['firstPage'] =   $url.'&'.$this->varPage."=1";
            $pageArray['endPage']   =   $url.'&'.$this->varPage."=$theEndRow";
            $pageArray['nextPages'] =   $url.'&'.$this->varPage."=$nextRow";
            $pageArray['prePages']  =   $url.'&'.$this->varPage."=$preRow";
            $pageArray['linkPages'] =   $linkPage;
            $pageArray['nowPage'] =   $this->nowPage;
            return $pageArray;
        }
        return $pageStr;
    }
}