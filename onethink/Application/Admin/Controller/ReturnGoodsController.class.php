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

class ReturnGoodsController extends AdminController {

    /**
     * 返还货物信息首页
     * @author 淘淘 <544828662@qq.com>
     */
    public function index(){
        $this->getMenu();
        if(!empty($_REQUEST["company_id"])){
            $map['company_id']   =   array('eq', $_REQUEST["company_id"]);
        }
        if(!empty($_REQUEST["condition"])){
            // $map['tel']   =   array('like', '%'.$_REQUEST["tel"].'%');
            $map['name|code|shelf|tel'] =array($_REQUEST["condition"],$_REQUEST["condition"],$_REQUEST["condition"],array('like', '%'.$_REQUEST["condition"].'%'),'_multi'=>true);
            $code = $_REQUEST["condition"];
        }
        if (!empty($_REQUEST['firstDate']) ) {
            $map['receive_time'][] = array('egt',strtotime(I('firstDate')));
            $firstDate = $_REQUEST['firstDate'];
        }
        if (!empty($_REQUEST['endDate']) ) {
            $map['receive_time'][] = array('elt', strtotime(I('endDate')));
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
        $list   = $this->lists('ReturnGoods', $map);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '返还物品信息';
        $default_time = date("Y-m-d");
        $this->assign('firstDate', $firstDate);
        $this->assign('endDate', $endDate);
        $this->assign('code', $code);
        $this->display();
    }

    /**
     * 新增问题件
     */
    public function add(){
        if(IS_POST){
            $sendonline = D('ReturnGoods');
            $data = $sendonline->create();
            if($data){
                if($id=$sendonline->add()){
                    action_log('addReturnGoods', 'ReturnGoods', $id, UID);
                    $this->success('新增成功');
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($sendonline->getError());
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
    public function export(){//导出Excel
        $xlsName  = "sendonline";
        $xlsCell  = array(
            array('id','账号序列'),
            array('name','收货人姓名'),
            array('code','快递单号')
        );
        $model = new Model();
        $xlsData  = $model->table(array(C('DB_PREFIX').'sendonline'=>'so',C('DB_PREFIX').'express'=>'e'))->field("so.id,so.name,so.code,so.shelf,so.tel,so.type,so.receive_time,so.city,e.id as company_id,e.name as company_name")->select();
        exportExcel($xlsName,$xlsCell,$xlsData);
        action_log('exportReturnGoods', 'ReturnGoods', 0, UID);
    }
    /**
     * 编辑即时货物信息
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function edit($id = 0){
        if(IS_POST){
            $sendonline = D('ReturnGoods');
            $data = $sendonline->create();
            if($data){
                if($sendonline->save()!== false){
                    // S('DB_CONFIG_DATA',null);
                    //记录行为
                    action_log('editReturnGoods', 'ReturnGoods', $data['id'], UID);
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
            $sendonline = M("ReturnGoods");
            $info = array();
            /* 获取数据 */
            $id = $_REQUEST["id"];
            $info = $sendonline->where("id=".$id)->find();
            $this->assign('info', $info);
            $this->meta_title = '编辑已发货货物信息';
            $this->display();
        }
    }
    /**
     * 删除货物信息
     * @author 淘淘
     */
    public function remove($ids = 0){
        empty($ids) && $this->error('参数错误！');
        if(is_array($ids)){
            $map['id'] = array('in', implode(',', $ids));
        }elseif (is_numeric($ids)){
            $map['id'] = $ids;
        }
        $res = M('ReturnGoods')->where($map)->delete();
        if($res !== false){
            action_log('RemoveReturnGoods', 'ReturnGoods', $ids, UID);
            $this->success('删除成功！');
        }else {
            $this->error('删除失败！');
        }
    }
    /**
     * 还原货物信息
     * @author 淘淘
     */
    public function retract($ids = 0){
        empty($ids) && $this->error('参数错误！');
        if(is_array($ids)){
            $map['id'] = array('in', implode(',', $ids));
        }elseif (is_numeric($ids)){
            $map['id'] = $ids;
        }

        $returngoods=M("ReturnGoods");
        $list=$returngoods->where($map)->select();
        foreach($list as $value){
            $info=$returngoods->where("id=".$value["id"])->find();
            action_log('retract', 'ReturnGoods', $value['id'], UID);
            $sendonline = M("Sendonline");
            $result = $sendonline->add($info);
            if(!false == $result){
                $sms=M("Sms");
                $sms->where("sid=".$value["id"])->delete();
                $returngoods->where("id=".$value["id"])->delete();
                // $this->success("取货成功");
            }
        }
        $this->success("恢复成功");
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
        $list   = $this->lists('ReturnGoods', $map);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '遣返物品信息';
        $default_time = date("Y-m-d");
        $this->assign('default_time', $default_time);
        $this->display();
    }
    /*
* 移动到回收站
*/
    public function del($ids=0){
        empty($ids) && $this->error('请选择货物！');
        if(is_array($ids)){
            foreach($ids as $value){
                $result = M('ReturnGoods')->where("id=".$value)->setField('is_recycle',1);
            }
            if($result !== false){
                action_log('del_returngoods', 'ReturnGoods', $ids, UID);
                $this->success('删除成功！');
            }else {
                $this->error('删除失败！');
            }
        }elseif (is_numeric($ids)){
            $result = M('ReturnGoods')->where("id=".$ids)->setField('is_recycle',1);
            if($result !== false){
                action_log('del_returngoods', 'ReturnGoods', $ids, UID);
                $this->success('删除成功！');
            }else {
                $this->error('删除失败！');
            }
        }

    }
    /*
* 还原
*/
    public function recover($ids=0){
        empty($ids) && $this->error('请选择货物！');
        if(is_array($ids)){
            foreach($ids as $value){
                $result = M('ReturnGoods')->where("id=".$value)->setField('is_recycle',0);
            }
            if($result !== false){
                action_log('recover_returngoods', 'ReturnGoods', $ids, UID);
                $this->success('还原成功！');
            }else {
                $this->error('还原失败！');
            }
        }elseif (is_numeric($ids)){
            $result = M('ReturnGoods')->where("id=".$ids)->setField('is_recycle',0);
            if($result !== false){
                action_log('recover_returngoods', 'ReturnGoods', $ids, UID);
                $this->success('还原成功！');
            }else {
                $this->error('还原失败！');
            }
        }

    }
}

