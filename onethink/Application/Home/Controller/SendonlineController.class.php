<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jiabin
 * Date: 13-12-9
 * Time: 下午1:09
 * To change this template use File | Settings | File Templates.
 */

namespace Home\Controller;
use Think\Controller;
use Think\Model;
class SendonlineController extends Controller{
    public function index() {
        $this->check();
        $method=$_REQUEST["method"];
        //执行请求的方法
        $this->$method();
        exit();
    }
    //检测请求是否合法
    private function check(){
        $method_array=array('getSendonlineList','addSendonline','updateSendonline','moveToSendover','moveToReturnGoods','getTodayCount','getCount','updateGoodsStatus','getPresentGoodsList','updateYuantong','updateOthers','deletePresentGoods','checkCode');
        $method = trim($_REQUEST["method"]);
        $sign = trim($_REQUEST["sign"]);
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
            exit;
        }
        return true;
    }
    /**
     * 即时发货列表接口
     * http://192.168.1.103/onethink/index.php?m=home&c=sendonline&a=info
     */
    public function getSendonlineList(){
        $uid = $_REQUEST["uid"];
        $city = getCity($uid);
        $page=empty($_REQUEST["page"])?1:$_REQUEST["page"];
        $pagesize=empty($_REQUEST["pagesize"])?15:$_REQUEST["pagesize"];//每页显示条数
        $start=($page-1)*$pagesize;//开始条数
        if(!empty($_REQUEST["condition"])){
            // $map['tel']   =   array('like', '%'.$_REQUEST["tel"].'%');
            $map['so.name|so.code|so.shelf|so.tel'] =array($_REQUEST["condition"],$_REQUEST["condition"],$_REQUEST["condition"],array('like', '%'.$_REQUEST["condition"].'%'),'_multi'=>true);
        }
        if(!empty($city)){
            $map['city']  = array('eq', $city);
        }
        $map['_string'] = 'so.company_id=e.id and so.is_recycle=0';
        $model=new Model();
        $result=$model->table(array(C('DB_PREFIX').'sendonline'=>'so',C('DB_PREFIX').'express'=>'e'))->where($map)->field("so.id,so.name as name,so.code as code,so.shelf,so.tel,so.type,so.receive_time,so.city,e.id as company_id,e.name as company_name")->limit($start,$pagesize)->select();
        $total=$model->table(array(C('DB_PREFIX').'sendonline'=>'so',C('DB_PREFIX').'express'=>'e'))->where($map)->count(); //总记录数
        if($result){
        echo $list='{"Rows":'.json_encode($result).',"Total":'.$total.',status:1}';
        }else{
            echo $list='{"Total":'.$total.',status:0}';
        }
    }
    /**
     * addSendline:新增即时货物信息
     */
    public function addSendonline(){
        $sendonline=M("Sendonline");
        $uid = $_REQUEST["uid"];
       $data["name"] = $_REQUEST["name"];
        $data["receive_time"]  = time();
        $data["tel"] = $_REQUEST["tel"];
        $data["company_id"] = $_REQUEST["company_id"];
        $data["shelf"]  =   $_REQUEST["shelf"];
        $data["city"] = $_REQUEST["city"];
        $data["type"] = $_REQUEST["type"];
        $data["code"] = $_REQUEST["code"];
        $validate = array(
            array('code', 'require', '快递单号不能为空'),
            array('code','','快递单号已经存在',0,'unique',1),
            array('city', 'require', '所在城市不能为空'),
            array('tel', 'require', '电话不能为空'),
            array('company_id', 'require', '物流公司不能为空'),
            array('shelf', 'require', '货架号不能为空'),
            array('type', 'require', '类型不能为空'),
        );
        $sendonline->setProperty("_validate",$validate);
        if($sendonline->create($data)){
            if($id = $sendonline->add($data)){
                $result["id"] = $id;
                $result["status"] = 1;
                $result["info"] = "新增成功";
                echo json_encode($result);
                action_log('add', 'Sendonline', $id, $uid);
            } else {
                $result["status"] = 0;
                $result["info"] = "新增失败";
                echo json_encode($result);
            }
        } else {
            $result["status"] = 0;
            $result["info"] = $sendonline->getError();
            echo json_encode($result);
        }
    }

    /**
     * 更新货物信息
     */
    public function updateSendonline(){
        $sendonline=M("Sendonline");
        $uid = $_REQUEST["uid"];
        $data["name"] = $_REQUEST["name"];
        $data["receive_time"]  = time();
        $data["tel"] = $_REQUEST["tel"];
        $data["company_id"] = $_REQUEST["company_id"];
        $data["shelf"]  =   $_REQUEST["shelf"];
        $data["city"] = $_REQUEST["city"];
        $data["type"] = $_REQUEST["type"];
        $data["code"] = $_REQUEST["code"];
        $id = $_REQUEST["id"];
        $validate = array(
           // array('name', 'require', '收货人姓名不能为空'),
            array('code', 'require', '快递单号不能为空'),
            array('city', 'require', '所在城市不能为空'),
            array('tel', 'require', '电话不能为空'),
            array('company_id', 'require', '物流公司不能为空'),
            array('shelf', 'require', '货架号不能为空'),
            array('type', 'require', '类型不能为空'),
        );
        $sendonline->setProperty("_validate",$validate);
        if($sendonline->create($data)){
            $result=$sendonline->where("id=".$id)->save($data);
            if(!false == $result){
                $msg["status"] = 1;
                $msg["info"] = "修改成功";
                echo json_encode($msg);
                action_log('update', 'Sendonline', $id, $uid);
             }else{
                $msg["status"] = 0;
                $msg["info"] = "修改失败";
                echo json_encode($msg);
            }
        }else{
            $msg["status"] = 0;
            $msg["info"] = $sendonline->getError();
            echo json_encode($msg);
        }
    }
    /**
     * 人来取货
     * 注：将该条数据迁移至已派货表中，然后删除与联系人关联的那条数据，最后删除本条数据
     * @param orderNum
     */
    public function moveToSendover(){
        $uid = $_REQUEST["uid"];
        $code = $_REQUEST["code"];
        if(!empty($code)){
            $sendonline=M("Sendonline");
            $list=$sendonline->where("code='".$code."'")->find();
            if(false == $list){
                $msg["status"] = 0;
                $msg["info"] = "不存在此单号";
                echo json_encode($msg);
                exit;
            }
            $sendover = M("Sendover");
            $list["send_time"] = time();
            $result = $sendover->add($list);
            if(!false == $result){
                $sms=M("Sms");
                $sms->where("sid=".$list["id"])->delete();
                $sendonline->where("code='".$code."'")->delete();
                $msg["status"] = 1;
                $msg["info"] = "迁移到已派货表成功";
                echo json_encode($msg);
                action_log('put', 'Sendonline', $list["id"], $uid);
            }
        }else{
            $msg["status"] = 0;
            $msg["info"] = "请传入快递单号";
            echo json_encode($msg);
        }
    }
    /**
     * 快递公司来取货
     * 注：将该条数据迁移至返还货表中，然后删除与联系人关联的那条数据，最后删除本条数据
     */
    public function moveToReturnGoods(){
        $uid = $_REQUEST["uid"];
        $code = $_REQUEST["code"];
        if(!empty($code)){
            $sendonline=M("Sendonline");
            $list=$sendonline->where("code='".$code."'")->find();
            if(false == $list){
                $msg["status"] = 0;
                $msg["info"] = "不存在此单号";
                echo json_encode($msg);
                exit();

            }
            $sendover = M("ReturnGoods");
            $list["back_time"] = time();
            $result = $sendover->add($list);
            if(!false == $result){
                $sms=M("Sms");
                $sms->where("sid=".$list["id"])->delete();
                $sendonline->where("code='".$code."'")->delete();
                $msg["status"] = 1;
                $msg["info"] = "迁移到返还表成功";
                echo json_encode($msg);
                action_log('back', 'Sendonline', $list["id"], $uid);
            }
        }else{
            $msg["status"] = 0;
            $msg["info"] = "请传入快递单号";
            echo json_encode($msg);
        }
    }
    /**
     * 入库时间为当天的该表的数量
     * @return
     */
    public function getTodayCount(){
        $uid = empty($_REQUEST["uid"])?1:$_REQUEST["uid"];
        $city =getCity($uid);
        $sendonline=M("Sendonline");
        $total=$sendonline->where("is_recycle=0 and FROM_UNIXTIME(receive_time,'%Y-%m-%d')=DATE_FORMAT(NOW() ,'%Y-%m-%d') and city='".$city."'")->count();
        $msg["status"] = 1;
        $msg["total"] = $total;
        echo json_encode($msg);
    }
    /**
     * 查询某个快递公司，某一段时间内(以入库时间为准)目前仍在及时派货表的货件数
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
        $type = $_REQUEST["type"];
        $map["city"] = array('eq', $city);
        if(!empty($type)&&$type == 4){
            $map["type"] = array("eq",4);
        }else{
            $map["type"] = array("neq",4);
        }
        if ( !empty($_REQUEST['firstDate'])&&!empty($_REQUEST['endDate'] ) ){
            $map['_string'].="is_recycle=0 and company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."'";
        }
        if($company_id&&$firstDate&&$endDate){
            $sendonline=M("Sendonline");
            $total=$sendonline->where($map)->count();
            $msg["status"] = 1;
            $msg["total"] = $total;
            echo json_encode($msg);
        }else{
            $msg["status"] = 0;
            $msg["info"] = "信息不完整";
            echo json_encode($msg);
        }
    }
    /**
     * 后台操作，将超时未取件修改成遗留
     * @param id
     * @param type  1:普通、2:同城、3:加急、4:遗留
     * @return
     */
    public function updateGoodsStatus(){
        $id = $_REQUEST["id"];
       // $type = $_REQUEST["type"];
        $sendonline=M("Sendonline");
        $result = $sendonline->where("id=".$id)->setField('type',4);
        if(!false==$result){
            $msg["status"] = 1;
            $msg["info"] = "修改成功";
            echo json_encode($msg);
        }else{
            $msg["status"] = 0;
            $msg["info"] = "修改失败";
            echo json_encode($msg);
        }
    }
    public function  updateYuantong(){
        $type = $_REQUEST["type"];
        $sendonline = M("Sendonline");
        if($type ==1){
            $result = $sendonline->where("is_recycle=0 and type=1 and company_id =3 and FROM_UNIXTIME(receive_time,'%Y-%m-%d')=curdate() and receive_time+6*3600<=unix_timestamp()")->select();
            $count = $sendonline->where("is_recycle=0 and type=1 and company_id =3 and FROM_UNIXTIME(receive_time,'%Y-%m-%d')=curdate() and receive_time+6*3600<=unix_timestamp()")->count();
            if($count>0){
                foreach($result as $value){
                    $sendonline->where("id=".$value["id"])->setField('type',4);
                }
            $msg["status"] = 1;
            $msg["total"] =$count;
            $msg["info"] = "修改成功";
            echo json_encode($msg);
            }else{
                $msg["status"] = 1;
                $msg["total"] =0;
                $msg["info"] = "无符合条件数据";
                echo json_encode($msg);
            }
        }else if($type == 3){
            $result = $sendonline->where("is_recycle=0 and type=3 and company_id =3 and FROM_UNIXTIME(receive_time,'%Y-%m-%d')=curdate() and receive_time+3*3600<=unix_timestamp()")->select();
            $count = $sendonline->where("is_recycle=0 and type=3 and company_id =3 and FROM_UNIXTIME(receive_time,'%Y-%m-%d')=curdate() and receive_time+3*3600<=unix_timestamp()")->count();
            if($count>0){
                foreach($result as $value){
                    $sendonline->where("id=".$value["id"])->setField('type',4);
                }
                $msg["status"] = 1;
                $msg["total"] =$count;
                $msg["info"] = "修改成功";
                echo json_encode($msg);
            }else{
                $msg["status"] = 1;
                $msg["total"] =0;
                $msg["info"] = "无符合条件数据";
                echo json_encode($msg);
            }
        }
    }
    public function updateOthers(){
        $type = $_REQUEST["type"];
        $sendonline = M("Sendonline");
        if(!empty($type)){
          if(date("H",time())>=18){
            $result = $sendonline->where("(type=2 and company_id =3) or company_id!=3 and is_recycle=0 and FROM_UNIXTIME(receive_time,'%Y-%m-%d')=curdate()  ")->select();
            $count = $sendonline->where("(type=2 and company_id =3) or company_id!=3 and is_recycle and FROM_UNIXTIME(receive_time,'%Y-%m-%d')=curdate() ")->count();
            if($count>0){
                foreach($result as $value){
                    $sendonline->where("id=".$value["id"])->setField('type',4);
                }
                $msg["status"] = 1;
                $msg["total"] =$count;
                $msg["info"] = "修改成功";
                echo json_encode($msg);

            }else{
                $msg["status"] = 0;
                $msg["total"] =$count;
                $msg["info"] = "无符合条件数据";
                echo json_encode($msg);
            }
          }
        }
    }
    public function getPresentGoodsList(){
        $uid = empty($_REQUEST["uid"])?1:$_REQUEST["uid"];
        $city =getCity($uid);
        $company_id = $_REQUEST["company_id"];
        $firstDate = date("Y-m-d",strtotime($_REQUEST["firstDate"]));
         $endDate = date("Y-m-d",strtotime($_REQUEST["endDate"]));
        $page=empty($_REQUEST["page"])?1:$_REQUEST["page"];
       $pagesize=empty($_REQUEST["pagesize"])?15:$_REQUEST["pagesize"];//每页显示条数
        $type=$_REQUEST["type"];
        $start=($page-1)*$pagesize;//开始条数
        $map["city"] = array('eq', $city);
        if($type ==4){
            $map["type"] =array("eq",4);
        }else{
            $map["type"] =array("neq",4);
        }
        if(!empty($company_id)){
            $map['_string'] = 'so.company_id=e.id and so.company_id='.$company_id;
        }

        if ( !empty($_REQUEST['firstDate'])&&!empty($_REQUEST['endDate'] ) ){
            $map['_string'].=" and is_recycle=0 and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."'";
        }
        if($company_id&&$firstDate&&$endDate){
            $model=new Model();
            $result=$model->table(array(C('DB_PREFIX').'sendonline'=>'so',C('DB_PREFIX').'express'=>'e'))->where($map)->field("so.id,so.name,so.code,so.shelf,so.tel,so.type,so.receive_time,so.city,e.id as company_id,e.name as company_name")->limit($start,$pagesize)->select();
            $total=$model->table(array(C('DB_PREFIX').'sendonline'=>'so',C('DB_PREFIX').'express'=>'e'))->where($map)->count(); //总记录数
            echo $list='{"Rows":'.json_encode($result).',"Total":'.$total.',status:1}';
        }else{
            $msg["status"] = 0;
            $msg["info"] = "信息不完整";
            echo json_encode($msg);
        }
    }
    /**
     * 删除及时派货件
     * @param id
     * @return
     */
public function  deletePresentGoods(){
    $ids = $_REQUEST["id"];
    $uid = $_REQUEST["uid"];
    empty($ids) && $this->error('请选择货物！');
    if(is_array($ids)){
        $map['id'] = array('in', implode(',', $ids));
    }elseif (is_numeric($ids)){
        $map['id'] = $ids;
    }
   // $res = M('Sendonline')->where($map)->delete();
    $res = M('Sendonline')->where("id=".$ids)->setField('is_recycle',1);
    $re=M("Sms")->where("sid=".$ids)->delete();
    if($res !== false){
        action_log('delete', 'Sendonline', $ids, $uid);
        $msg["status"] = 1;
        $msg["info"] = "删除成功";
        echo json_encode($msg);
    }else {
        $msg["status"] = 0;
        $msg["info"] = "删除失败";
        echo json_encode($msg);
    }
}
    public function checkCode(){
        $code = $_REQUEST["code"];
        $result = M("Sendonline")->where("code='".$code."'")->find();
        $result1 = M("Sendover")->where("code='".$code."'")->find();
        $result2 = M("ReturnGoods")->where("code='".$code."'")->find();
        if(!$result&&!$result1&&!$result2){
            $msg["status"] = 0;
            $msg["info"] = "单号可用";
            echo json_encode($msg);
        }else{
         if($result){
             $msg["status"] = 1;
             $msg["info"] = "单号已存在";
             echo json_encode($msg);
         }else if($result1){
             $msg["status"] = 2;
             $msg["info"] = "单号已存在";
             echo json_encode($msg);
         }else{
             $msg["status"] = 3;
             $msg["info"] = "单号已存在";
             echo json_encode($msg);
         }
        }
    }
}