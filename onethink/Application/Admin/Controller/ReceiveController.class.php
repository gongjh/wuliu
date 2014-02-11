<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jiabin
 * Date: 13-12-17
 * Time: 下午5:53
 * To change this template use File | Settings | File Templates.
 */

namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use Think\Model;

class ReceiveController extends AdminController{
    public function index(){
        $this->getMenu();
        if(!empty($_REQUEST["condition"])){
            // $map['tel']   =   array('like', '%'.$_REQUEST["tel"].'%');
            $map['send_name|code|send_phone'] =array($_REQUEST["condition"],$_REQUEST["condition"],array('like', '%'.$_REQUEST["condition"].'%'),'_multi'=>true);
        }
        if(!empty($_REQUEST["company_id"])){
            $map['company_id']   =   array('eq', $_REQUEST["company_id"]);
        }
        if ( !empty($_REQUEST['firstDate'])&&!empty($_REQUEST['endDate'] ) ){
            $map['_string'].=" FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$_REQUEST['firstDate']."' and '".$_REQUEST['endDate']."'";
        }
        if(UID !=1){
            $city =getCity(UID);
            $map['city']  = array('eq', $city);
        }

        $company=M("Express");
        $companylist=$company->select();
        $this->assign('clist', $companylist);
        $list   = $this->lists('Receive', $map);
        int_to_string($list);
        $this->assign('_list', $list);
        $result = M("Receive")->where($map)->field("sum(price) as total_price,count(*) as count")->find();
        $result["firstDate"] = $_REQUEST["firstDate"];
        $result["endDate"] = $_REQUEST["endDate"];
        $result["company_id"] = $_REQUEST["company_id"];
        $this->assign('list', $result);
        $this->meta_title = '收货物品信息';
        $default_time = date("Y-m-d");
        $this->assign('default_time', $default_time);
        $this->display();
    }

    /**
     * 新增即时发货信息
     */
    public function add(){
        if(IS_POST){
            $receive = D('Receive');
            $data = $receive->create();
            if($data){
                if($id=$receive->add()){
                    action_log('add', 'Receive', $id, UID);
                    $this->success('新增成功');
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($receive->getError());
            }
        } else {
            $city =getCity(UID);
            $this->assign('city', $city);
            $this->getMenu();
            $company=M("Express");
            $companylist=$company->select();
            $this->assign('list', $companylist);
            $this->display();
        }

    }
    /**
     * 显示左边菜单，进行权限控制
     * @author 淘淘 <544828662@qq.com>
     */
    protected function getMenu(){
        //获取动态分类
        $cate_auth  =   AuthGroupModel::getAuthCategories(UID);	//获取当前用户所有的内容权限节点
        $cate_auth  =	$cate_auth == null ? array() : $cate_auth;
        //获取回收站权限
        $show_recycle = $this->checkRule('Admin/article/recycle');
        $this->assign('show_recycle', IS_ROOT || $show_recycle);
        //获取草稿箱权限
        $show_draftbox = C('OPEN_DRAFTBOX');
        $this->assign('show_draftbox', IS_ROOT || $show_draftbox);
    }
    public function export(){
        //导出Excel
        if(IS_POST){
           // $city = I("city");
            $start = I("start");
            $end = I("end");
            $wuliu = I("company_id");
            if(!empty($city)){
                $map['city']  = array('eq', $city);
            }
            if(!empty($wuliu)){
                $map['company_id']  = array('eq', $wuliu);
                // $map["_string"] = " and so.company_id =".$wuliu;

            }
            $map["_string"] = "so.company_id =e.id";
            $xlsName  = "收货货物信息";
            $xlsCell  = array(
                array('receive_name','收货人姓名'),
                array('receive_address','收货人地址'),
                array('receive_phone','收货人电话'),
                array('send_name','寄件人姓名'),
                array('send_address','寄件人地址'),
                array('send_phone','寄件人电话'),
                array('code','快递单号'),
                array('receive_time','入库时间'),
                array('company_name','物流公司'),
                array('weight','重量'),
                array('price','价格')
            );
            $model = new Model();
            $xlsData  = $model->table(array(C('DB_PREFIX').'receive'=>'so',C('DB_PREFIX').'express'=>'e'))->where($map)->field("so.id,so.receive_name,so.weight,so.price,so.receive_address,so.receive_phone,so.send_name,so.send_address,so.send_phone,so.code,FROM_UNIXTIME(so.receive_time,  '%Y-%m-%d' ) AS receive_time,e.name as company_name")->select();
         //echo $model->getLastSql();
         exportExcel($xlsName,$xlsCell,$xlsData);
        }else{
            $company=M("Express");
            $companylist=$company->select();
            $this->assign('list', $companylist);
            if(UID !=1){
                $city =getCity(UID);
                $this->assign("city",$city);
            }
            $this->display();
        }
    }
    /**
     * 编辑即时货物信息
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function edit($id = 0){
        if(IS_POST){
            $receive = D('Receive');
            $data = $receive->create();
            if($data){
                if($receive->save()!== false){
                    // S('DB_CONFIG_DATA',null);
                    //记录行为
                    action_log('edit', 'Receive', $data['id'], UID);
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($receive->getError());
            }
        } else {
            $wuliu = M("Express");
            $list = $wuliu->select();
            $this->assign('list', $list);
            $receive = M("Receive");
            $info = array();
            /* 获取数据 */
         $id = $_REQUEST["id"];
            $info = $receive->where("id=".$id)->find();

            $this->assign('info', $info);
            $this->meta_title = '编辑即时发货货物信息';
            $this->display();
        }
    }
    /**
     * 删除货物信息
     * @author 淘淘
     */
    public function remove($ids = 0){
        empty($ids) && $this->error('请选择货物！');
        if(is_array($ids)){
            $map['id'] = array('in', implode(',', $ids));
        }elseif (is_numeric($ids)){
            $map['id'] = $ids;
        }
        $res = M('Receive')->where($map)->delete();
        if($res !== false){
            $this->success('删除成功！');
        }else {
            $this->error('删除失败！');
        }
    }
    public function getPrice(){
        $weight = trim($_POST["weight"]);
         $addr = trim($_POST["receive_address"]);
        $company_id = $_POST["company_id"];
        $discount = trim($_POST["discount"]);
        $pack = trim($_POST["pack"]);
        $city =getCity(UID);
        $ap = M("AreaPrice");
        $result = $ap->where("city='".$city."' and company_id=".$company_id)->find();
        $result = $ap->where("company_id=".$company_id." and area like '%".$addr."%'")->find();
        if($weight>0){
      echo $price = $result["price"]+(ceil($weight)-1)*$result["plusprice"]-$discount+$pack;
        }else{
            echo $price =0;
        }

    }
    public function tongji(){
        $this->getMenu();
        $firstDate = empty($_REQUEST["firstDate"])?date("Y-m-d"):$_REQUEST["firstDate"];
        $endDate = empty($_REQUEST["endDate"])?date("Y-m-d"):$_REQUEST["endDate"];
        $company_id=$_REQUEST["company_id"];
        $model = new Model();
        if($company_id==0){
            $kd = M("express");
            $res = $kd->select();
            foreach($res as $value){
                $company_id = $value["id"];
                $list[$value["id"]]  = $model->query("
 select (select count(*) as count from ".C('DB_PREFIX')."receive where company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as count,(select sum(price) as count from ".C('DB_PREFIX')."receive where company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as price,name from ".C('DB_PREFIX')."express where id=".$company_id);
 }

        }else{
            $list  = $model->query("
        select
        (select count(*) from ".C('DB_PREFIX')."receive where type!=4 and company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as ccount,
        (select count(*) from ".C('DB_PREFIX')."receive where type=4 and company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as ocount,
        (select count(*) from ".C('DB_PREFIX')."sendover where company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as vcount,
        (select count(*) from ".C('DB_PREFIX')."return_goods where  company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as rcount,
        name from ".C('DB_PREFIX')."express where id=".$company_id);
            //
        }
        $this->assign('_list', $list);
        $this->assign('company_id', $_REQUEST["company_id"]);
        $this->meta_title = '统计数据信息';
        $company=M("Express");
        $companylist=$company->select();
        $this->assign('list', $companylist);
        $this->display();
    }

}