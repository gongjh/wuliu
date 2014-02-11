<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use Think\Model;
/**
 * 后台即时发货控制器
 * @author 淘淘
 */

class SendonlineController extends AdminController {

    /**
     * 用户管理首页
     * @author 淘淘 <544828662@qq.com>
     */
    public function index(){

        $this->getMenu();
        if(!empty($_REQUEST["condition"])){
           // $map['tel']   =   array('like', '%'.$_REQUEST["tel"].'%');
            $map['name|code|shelf|tel'] =array($_REQUEST["condition"],$_REQUEST["condition"],$_REQUEST["condition"],array('like', '%'.$_REQUEST["condition"].'%'),'_multi'=>true);
            $code = $_REQUEST["condition"];
        }
        if(!empty($_REQUEST["company_id"])){
            $map['company_id']   =   array('eq', $_REQUEST["company_id"]);
        }
        if(!empty($_REQUEST["type"])){
            $map['type']  =   $_REQUEST["type"];
       }else{
            $map['type']  =   array('in', '1,2,3,4');
        }
        if (!empty($_REQUEST['firstDate']) ) {
            $map['receive_time'][] = array('egt',strtotime(I('firstDate')));
            $firstDate = $_REQUEST['firstDate'];
        }
        if (!empty($_REQUEST['endDate']) ) {
            $map['receive_time'][] = array('elt',24*60*60 + strtotime(I('endDate')));
            $endDate = $_REQUEST['endDate'];
        }
        if(UID !=1){
            $city =getCity(UID);
            $map['city']  = array('eq', $city);
        }
        $map['is_recycle']   =   array('eq', 0);
        $company=M("Express");
        $companylist=$company->select();
        $this->assign('list', $companylist);
        $list   = $this->lists('Sendonline', $map);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '即时发货物品信息';
        $default_time = date("Y-m-d");
        $this->assign('firstDate', $firstDate);
        $this->assign('endDate', $endDate);
        $this->assign('code', $code);
        $this->display();
    }

    /**
     * 新增即时发货信息
     */
    public function add(){
        if(IS_POST){
            $sendonline = D('Sendonline');
            $data = $sendonline->create();
            if($data){
                if($id=$sendonline->add()){
                    action_log('add', 'Sendonline', $id, UID);
                    $data["sid"] = $id;
                    M("Sms")->add($data);
                    $this->success('新增成功');
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($sendonline->getError());
            }
        } else {
            $city =getCity(UID);
            $this->getMenu();
            $company=M("Express");
            $companylist=$company->select();
            $this->assign('city', $city);
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
            $city = I("city");
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
        $xlsName  = "即时发货货物信息";
        $xlsCell  = array(
            array('name','收货人姓名'),
            array('code','快递单号'),
            array('shelf','货架号'),
            array('tel','电话号码'),
            array('type','类型'),
            array('receive_time','入库时间'),
            array('city','所在站点'),
            array('company_name','物流公司')

        );
        $model = new Model();
        $xlsData  = $model->table(array(C('DB_PREFIX').'sendonline'=>'so',C('DB_PREFIX').'express'=>'e'))->where($map)->field("so.id,so.name,so.code,so.shelf,so.tel,so.type,FROM_UNIXTIME(so.receive_time,  '%Y-%m-%d' ) AS receive_time,so.city,e.name as company_name")->select();
     // $model->getLastSql();
         exportExcel($xlsName,$xlsCell,$xlsData);
         action_log('export', 'Sendonline', 0, UID);
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
            $sendonline = D('Sendonline');
            $data = $sendonline->create();
            if($data){
                if($sendonline->save()!== false){
                    // S('DB_CONFIG_DATA',null);
                    //记录行为
                    action_log('update', 'Sendonline', $data['id'], UID);
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($sendonline->getError());
            }
        } else {
            $wuliu = M("Express");
            $list = $wuliu->select();
            $this->assign('list', $list);
            $sendonline = M("Sendonline");
            $info = array();
            /* 获取数据 */
           $id = $_REQUEST["id"];
            $info = $sendonline->where("id=".$id)->find();
            $this->assign('info',$info);
            $this->meta_title = '编辑即时发货货物信息';
            $this->display();
        }
    }
    public function remark(){
        if(IS_POST){
            $sendonline = D('Sendonline');
            $data = $sendonline-> where('id='.$_REQUEST["id"])->setField('remark',$_REQUEST["remark"]);
            if($data){
                    action_log('remark', 'Sendonline', $_REQUEST["id"], UID);
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }

        } else {
            $sendonline = M("Sendonline");
            $info = array();
            /* 获取数据 */
            $id = $_REQUEST["id"];
            $info = $sendonline->where("id=".$id)->find();
            $this->assign('info',$info);
            $this->meta_title = '编辑备注';
            $this->display();
        }
    }
    public function put($ids=0){
        empty($ids) && $this->error('参数错误！');
        if(is_array($ids)){
            $map['id'] = array('in', implode(',', $ids));
        }elseif (is_numeric($ids)){
            $map['id'] = $ids;
        }

       $sendonline=M("Sendonline");
      $list=$sendonline->where($map)->select();
        foreach($list as $value){
            $info=$sendonline->where("id=".$value["id"])->find();
            action_log('put', 'Sendonline', $value['id'], UID);
            $sendover = M("Sendover");
            $info["send_time"] = time();
            $result = $sendover->add($info);
            if(!false == $result){
                $sms=M("Sms");
                $sms->where("sid=".$value["id"])->delete();
                $sendonline->where("id=".$value["id"])->delete();
               // $this->success("取货成功");
            }
        }
        $this->success("取货成功");
    }
    public function back($ids=0){
        empty($ids) && $this->error('请选择货物！');
        if(is_array($ids)){
            $map['id'] = array('in', implode(',', $ids));
        }elseif (is_numeric($ids)){
            $map['id'] = $ids;
        }

        $sendonline=M("Sendonline");
        $list=$sendonline->where($map)->select();
        foreach($list as $value){
            $info=$sendonline->where("id=".$value["id"])->find();
            $sendover = M("ReturnGoods");
            $info["back_time"] = time();
            $result = $sendover->add($info);
            action_log('back', 'Sendonline', $value['id'], UID);
            if(!false == $result){
                $sms=M("Sms");
                $sms->where("sid=".$value["id"])->delete();
                $sendonline->where("id=".$value["id"])->delete();
                // $this->success("取货成功");
            }
        }
        $this->success("遣返成功");
    }
    /*
     * 移动到回收站
     */
    public function del($ids=0){
        empty($ids) && $this->error('请选择货物！');
        if(is_array($ids)){
              foreach($ids as $value){
            $result = M('Sendonline')->where("id=".$value)->setField('is_recycle',1);
              }
            if($result !== false){
                action_log('del', 'Sendonline', $ids, UID);
                $this->success('删除成功！');
            }else {
                $this->error('删除失败！');
            }
        }elseif (is_numeric($ids)){
            $result = M('Sendonline')->where("id=".$ids)->setField('is_recycle',1);
            if($result !== false){
                action_log('del', 'Sendonline', $ids, UID);
                $this->success('删除成功！');
            }else {
                $this->error('删除失败！');
            }
        }

    }
    /**
     * 彻底删除货物信息
     * @author 淘淘
     */
    public function remove($ids = 0){
        empty($ids) && $this->error('请选择货物！');
        if(is_array($ids)){
            $map['id'] = array('in', implode(',', $ids));
        }elseif (is_numeric($ids)){
            $map['id'] = $ids;
        }
        $res = M('Sendonline')->where($map)->delete();
        if($res !== false){
            action_log('delete', 'Sendonline', $ids, UID);
            $this->success('删除成功！');
        }else {
            $this->error('删除失败！');
        }
    }
    public function recycle(){
        $this->getMenu();
        if(!empty($_REQUEST["condition"])){
            // $map['tel']   =   array('like', '%'.$_REQUEST["tel"].'%');
            $map['name|code|shelf|tel'] =array($_REQUEST["condition"],$_REQUEST["condition"],$_REQUEST["condition"],array('like', '%'.$_REQUEST["condition"].'%'),'_multi'=>true);
        }
        if(!empty($_REQUEST["company_id"])){
            $map['company_id']   =   array('eq', $_REQUEST["company_id"]);
        }
        if(!empty($_REQUEST["type"])){
            $map['type']  =   $_REQUEST["type"];
        }else{
            $map['type']  =   array('in', '1,2,3,4');
        }
        if (!empty($_REQUEST['firstDate']) ) {
            $map['receive_time'][] = array('egt',strtotime(I('firstDate')));
        }
        if (!empty($_REQUEST['endDate']) ) {
            $map['receive_time'][] = array('elt',24*60*60 + strtotime(I('endDate')));
        }
        if(UID !=1){
            $city =getCity(UID);
            $map['city']  = array('eq', $city);
        }
        $map['is_recycle']   =   array('eq', 1);
        $company=M("Express");
        $companylist=$company->select();
        $this->assign('list', $companylist);
        $list   = $this->lists('Sendonline', $map);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '即时发货物品信息';
        $default_time = date("Y-m-d");
        $this->assign('default_time', $default_time);
        $this->display();
    }
    /*
    * 还原
   */
    public function recover($ids=0){
        empty($ids) && $this->error('请选择货物！');
        if(is_array($ids)){
            foreach($ids as $value){
                $result = M('Sendonline')->where("id=".$value)->setField('is_recycle',0);
            }
            if($result !== false){
                action_log('recover', 'Sendonline', $ids, UID);
                $this->success('还原成功！');
            }else {
                $this->error('还原失败！');
            }
        }elseif (is_numeric($ids)){
            $result = M('Sendonline')->where("id=".$ids)->setField('is_recycle',0);
            if($result !== false){
                action_log('recover', 'Sendonline', $ids, UID);
                $this->success('还原成功！');
            }else {
                $this->error('还原失败！');
            }
        }

    }

}

