<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jiabin
 * Date: 13-12-11
 * Time: 上午9:26
 * To change this template use File | Settings | File Templates.
 */

namespace Home\Controller;


use Think\Controller;
use Think\Model;

class SmsController extends Controller{
    public function index() {
        $this->check();
        $method=$_REQUEST['method'];
        //执行请求的方法
        $this->$method();
        exit();
    }
    //检测请求是否合法
    private function check(){
        $method_array=array('getSmsList','addContacts','updateSmsStatus','updateRemark','deleteContacts');
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
     * 即时发货列表接口
     * http://192.168.1.103/onethink/index.php?m=home&c=sendonline&a=info
     */
    public function getSmsList(){
        $uid = empty($_REQUEST["uid"])?1:$_REQUEST["uid"];
        $city =getCity($uid);
        $model=new Model();
        $page=empty($_REQUEST["page"])?1:$_REQUEST["page"];
        $pagesize=empty($_REQUEST["pagesize"])?15:$_REQUEST["pagesize"];//每页显示条数
        $sms_status = $_REQUEST["sms_status"];
        $start=($page-1)*$pagesize;//开始条数
        $result=$model->table(array(C('DB_PREFIX').'sendonline'=>'so',C('DB_PREFIX').'sms'=>'sms',C('DB_PREFIX').'express'=>'e'))->where("so.is_recycle=0 and so.company_id=e.id and so.id=sms.sid and sms.sms_status=".$sms_status." and so.city='".$city."'")->field("so.*,e.name as company_name,sms.*")->limit($start,$pagesize)->select();
        $total=$model->table(array(C('DB_PREFIX').'sendonline'=>'so',C('DB_PREFIX').'sms'=>'sms'))->where("so.is_recycle=0 and so.id=sms.sid and sms.sms_status=".$sms_status." and so.city='".$city."'")->count(); //总记录数
        if($result){
            echo $list='{"Rows":'.json_encode($result).',"Total":'.$total.',status:1}';
        }else{
            echo $list='{"Rows":'.json_encode($result).',status:0}';
        }
    }

    public function addContacts(){
        $sms=M("Sms");
        $uid = empty($_REQUEST["uid"])?1:$_REQUEST["uid"];
        $data["sid"] = $_REQUEST["sid"];
        $data["sms_status"] = $_REQUEST["sms_status"];
        $data["goods_status"] = $_REQUEST["goods_status"];
        $data["comment"]  =   $_REQUEST["comment"];
        $validate = array(
            array('sid', 'require', '货物id不能为空'),
            array('sms_status', 'require', '短信状态不能为空'),
            array('sid','','已经存在',0,'unique',1),
            array('goods_status', 'require', '货物状态不能为空')
        );
        $sms->setProperty("_validate",$validate);
        if($sms->create($data)){
            if($id = $sms->add($data)){
                $result["status"] = 1;
                $result["info"] = "新增成功";
                echo json_encode($result);
                action_log('addContacts', 'Sms', $id, $uid);
            } else {
                $result["status"] = 0;
                $result["info"] = "新增失败";
                echo json_encode($result);
            }
        } else {
            $result["status"] = 0;
            $result["info"] = $sms->getError();
            echo json_encode($result);
        }
    }
    public function updateSmsStatus(){
         $id = $_REQUEST["id"];
         $sms_status = $_REQUEST["sms_status"];
        $uid = empty($_REQUEST["uid"])?1:$_REQUEST["uid"];
        if(empty($id) or empty($sms_status)){
            $msg["status"] = 0;
            $msg["info"] = "缺少参数";
            echo json_encode($msg);
            exit;
        }else{
            $sms=M("Sms");
            $status=$sms->where("id=".$id)->find();
            if($sms_status==1&&$status["sms_status"] == 1){
                $msg["status"] = 1;
                $msg["info"] = "修改成功";
                echo json_encode($msg);
                action_log('updateSmsStatus', 'Sms', $id, $uid);
                exit;
            }
            if($sms_status==0&&$status["sms_status"] == 0){
                $msg["status"] = 1;
                $msg["info"] = "修改失败";
                echo json_encode($msg);
                exit;
            }
        }
        $result = $sms->where("id=".$id)->setField('sms_status',$sms_status);
        if($result){
            $msg["status"] = 1;
            $msg["info"] = "修改成功";
            echo json_encode($msg);
        }else{
            $msg["status"] = 0;
            $msg["info"] = "修改失败";
            echo json_encode($msg);
        }
    }
    public function updateRemark(){
        $id = $_REQUEST["id"];
        $comment = $_REQUEST["comment"];
        $uid = empty($_REQUEST["uid"])?1:$_REQUEST["uid"];
        if(empty($id) or empty($comment)){
            $msg["status"] = 0;
            $msg["info"] = "缺少参数";
            echo json_encode($msg);
            exit;
        }
        $sms=M("Sms");
        $result = $sms->where("id=".$id)->setField('comment',$comment);
        if(!false==$result){
            $msg["status"] = 1;
            $msg["info"] = "修改成功";
            echo json_encode($msg);
            action_log('updateRemark', 'Sms', $id, $uid);
        }else{
            $msg["status"] = 0;
            $msg["info"] = "修改失败";
            echo json_encode($msg);
        }
    }
/* 删除联系人
* @param id
* @return
*/
    public function  deleteContacts(){
        $ids = $_REQUEST["id"];
        $uid = $_REQUEST["uid"];
        empty($ids) && $this->error('请选择ID！');
        if(is_array($ids)){
            $map['id'] = array('in', implode(',', $ids));
        }elseif (is_numeric($ids)){
            $map['id'] = $ids;
        }
        $res = M('Sms')->where($map)->delete();
        if($res !== false){
            action_log('removeSms', 'Sms', $ids, $uid);
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