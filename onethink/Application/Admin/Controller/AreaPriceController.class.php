<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jiabin
 * Date: 13-12-26
 * Time: 下午1:19
 * To change this template use File | Settings | File Templates.
 */

namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use Think\Model;

class AreaPriceController extends AdminController{
    /**
     * 列表页
     * @author 淘淘 <544828662@qq.com>
     */
    public function index(){
        $this->getMenu();
        if(UID !=1){
            $city =getCity(UID);
            $map['city']  = array('eq', $city);
        }
        $list   = $this->lists('AreaPrice', $map);
        int_to_string($list);
       // $company=M("AreaPrice");
       // $companylist=$company->select();
        $this->assign('_list', $list);
        $this->meta_title = '快递价格信息';
        $this->display();
    }
    /**
     * 新增快递区域价格信息
     */
    public function add(){
        if(IS_POST){
            $ap = D('AreaPrice');
            $data = $ap->create();
            if($data){
                if($id=$ap->add()){
                    action_log('add', 'AreaPrice', $id, UID);
                    $this->success('新增成功');
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($ap->getError());
            }
        } else {
            $this->getMenu();
            $company=M("Express");
            $companylist=$company->select();
            $this->assign('list', $companylist);
            $this->display();
        }

    }
    /**
     * 编辑即时货物信息
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function edit($id = 0){
        if(IS_POST){
            $sendonline = D('AreaPrice');
            $data = $sendonline->create();
            if($data){
                if($sendonline->save()!== false){
                    // S('DB_CONFIG_DATA',null);
                    //记录行为
                    action_log('update_areaprice', 'AreaPrice', $data['id'], UID);
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
            $sendonline = M("AreaPrice");
            $info = array();
            /* 获取数据 */
            $id = $_REQUEST["id"];
            $info = $sendonline->where("id=".$id)->find();
            $this->assign('info',$info);
            $this->meta_title = '编辑货物信息';
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
    }
}