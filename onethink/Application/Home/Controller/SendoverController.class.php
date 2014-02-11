<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jiabin
 * Date: 13-12-10
 * Time: 下午5:54
 * To change this template use File | Settings | File Templates.
 */

namespace Home\Controller;
use Think\Controller;
use Think\Model;

class SendoverController extends Controller {
    public function index() {
        $this->check();
        $method=$_REQUEST['method'];
        //执行请求的方法
        $this->$method();
        exit();
    }
    //检测请求是否合法
    private function check(){
        $method_array=array('getTodayCount','getCount','getSentGoodsList','deleteSentGoods');
        $method = $_REQUEST["method"];
        $sign = $_REQUEST["sign"];
        //签名方式
        $key='wuliu2013';	//双方约定的一个key
        $my_sign=md5($key);
        if(!in_array($method, $method_array)){
            $result["status"] = 0;
            $result["info"] = "请求的方法不存在";
            echo json_encode($result);
        }
        if($my_sign!=$sign){
            $result["status"] = 0;
            $result["info"] = "签名不正确";
            echo json_encode($result);
        }
        return true;
    }
    /**
     * 入库时间为当天的该表的数量
     * @return
     */
    public function getTodayCount(){
        $uid = empty($_REQUEST["uid"])?1:$_REQUEST["uid"];
        $city =getCity($uid);
        $sendover=M("Sendover");
        $total=$sendover->where("is_recycle=0 and FROM_UNIXTIME(receive_time,'%Y-%m-%d')=DATE_FORMAT(NOW() ,'%Y-%m-%d') and city='".$city."'")->count();
        $msg["status"] = 1;
        $msg["total"] = $total;
        echo json_encode($msg);
    }
    /**
     * 查询某个快递公司，某一段时间内(以入库时间为准)目前仍在已派货表的货件数
     * @param companyId
     * @param firstDate
     * @param endDate
     * @return
     */
    public function getCount(){
        $uid = empty($_REQUEST["uid"])?1:$_REQUEST["uid"];
        $city =getCity($uid);
        $company_id = $_REQUEST["company_id"];
        $firstDate = empty($_REQUEST["firstDate"])?date("Y-m-d"):date("Y-m-d",strtotime($_REQUEST["firstDate"]));
        $endDate = empty($_REQUEST["endDate"])?date("Y-m-d"):date("Y-m-d",strtotime($_REQUEST["endDate"]));
        if($company_id&&$firstDate&&$endDate){
            $sendover=M("Sendover");
            $total=$sendover->where("is_recycle=0 and company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."' and city='".$city."'")->count();
            $msg["status"] = 1;
            $msg["total"] = $total;
            echo json_encode($msg);
        }else{
            $msg["status"] = 0;
            $msg["info"] = "信息不完整";
            echo json_encode($msg);
        }
    }
    public function getSentGoodsList(){
        $uid = empty($_REQUEST["uid"])?1:$_REQUEST["uid"];
        $city =getCity($uid);
        $company_id = $_REQUEST["company_id"];
        $firstDate = date("Y-m-d",strtotime($_REQUEST["firstDate"]));
        $endDate = date("Y-m-d",strtotime($_REQUEST["endDate"]));
        $page=empty($_REQUEST["page"])?1:$_REQUEST["page"];
        $pagesize=empty($_REQUEST["pagesize"])?15:$_REQUEST["pagesize"];//每页显示条数
        $start=($page-1)*$pagesize;//开始条数
       $map["city"] = array('eq', $city);
        if(!empty($company_id)){
            //$map['company_id']   =   array('eq', $_REQUEST["company_id"]);
            $map['_string'] = 'so.company_id=e.id and so.company_id='.$company_id;
        }

        if ( !empty($_REQUEST['firstDate'])&&!empty($_REQUEST['endDate'] ) ){
            $map['_string'].=" and is_recycle=0 and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."'";
        }
        if($company_id&&$firstDate&&$endDate){
            $model=new Model();
            $result=$model->table(array(C('DB_PREFIX').'sendover'=>'so',C('DB_PREFIX').'express'=>'e'))->where($map)->field("so.id,so.name,so.code,so.shelf,so.tel,so.type,so.receive_time,so.city,so.send_time,e.id as company_id,e.name as company_name")->limit($start,$pagesize)->select();
            $total=$model->table(array(C('DB_PREFIX').'sendover'=>'so',C('DB_PREFIX').'express'=>'e'))->where($map)->count(); //总记录数
            echo $list='{"Rows":'.json_encode($result).',"Total":'.$total.',status:1}';
        }else{
            $msg["status"] = 0;
            $msg["info"] = "信息不完整";
            echo json_encode($msg);
        }
    }
    /**
     * 删除已派货件
     * @param id
     * @return
     */
public function  deleteSentGoods(){
    $ids = $_REQUEST["id"];
    $uid = $_REQUEST["uid"];
    empty($ids) && $this->error('请选择货物！');
    if(is_array($ids)){
        $map['id'] = array('in', implode(',', $ids));
    }elseif (is_numeric($ids)){
        $map['id'] = $ids;
    }
   // $res = M('Sendover')->where($map)->delete();
    $res = M('Sendover')->where("id=".$ids)->setField('is_recycle',1);
    $re=M("Sms")->where("sid=".$ids)->delete();
    if($res !== false){
        action_log('removeSendover', 'Sendover', $ids, $uid);
        $msg["status"] = 1;
        $msg["info"] = "删除成功";
        echo json_encode($msg);
    }else {
        $msg["status"] = 0;
        $msg["info"] = "删除失败";
        echo json_encode($msg);
    }
}
}