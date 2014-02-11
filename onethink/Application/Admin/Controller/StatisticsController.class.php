<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jiabin
 * Date: 13-12-16
 * Time: 下午1:17
 * To change this template use File | Settings | File Templates.
 */

namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use Think\Model;

class StatisticsController extends AdminController{
    public function index(){
	        if(UID !=1){
            $city =getCity(UID);
            
        }
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
        select
        (select count(*) from ".C('DB_PREFIX')."sendonline where city='".$city."' and type!=4 and company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as ccount,
        (select count(*) from ".C('DB_PREFIX')."sendonline where city='".$city."' and type=4 and company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as ocount,
        (select count(*) from ".C('DB_PREFIX')."sendover where city='".$city."' and company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as vcount,
        (select count(*) from ".C('DB_PREFIX')."return_goods where  city='".$city."' and company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as rcount,
        name from ".C('DB_PREFIX')."express where id=".$company_id);

        }

        }else{
            $list  = $model->query("
        select
        (select count(*) from ".C('DB_PREFIX')."sendonline where city='".$city."' and type!=4 and company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as ccount,
        (select count(*) from ".C('DB_PREFIX')."sendonline where city='".$city."' and type=4 and company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as ocount,
        (select count(*) from ".C('DB_PREFIX')."sendover where city='".$city."' and company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as vcount,
        (select count(*) from ".C('DB_PREFIX')."return_goods where  city='".$city."' and company_id=".$company_id." and FROM_UNIXTIME(receive_time,'%Y-%m-%d') between '".$firstDate."' and '".$endDate."') as rcount,
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
}