<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jiabin
 * Date: 13-12-30
 * Time: 下午4:33
 * To change this template use File | Settings | File Templates.
 */

namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use Think\Model;

class SmsController extends AdminController{
        public function index(){
            $this->getMenu();
            $city =getCity(UID);
            $model=new Model();
            $page=empty($_REQUEST["page"])?1:$_REQUEST["page"];
            $pagesize=empty($_REQUEST["pagesize"])?15:$_REQUEST["pagesize"];//每页显示条数
           // $sms_status = $_REQUEST["sms_status"];
            $start=($page-1)*$pagesize;//开始条数
            $where="so.company_id=e.id and so.id=sms.sid";
            if(UID!=1){
                $where.=" and so.city='".$city."'";
            }
            $result=$model->table(array(C('DB_PREFIX').'sendonline'=>'so',C('DB_PREFIX').'sms'=>'sms',C('DB_PREFIX').'express'=>'e'))->where($where)->field("so.*,e.name as company_name,sms.*")->limit($start,$pagesize)->select();
            $total=$model->table(array(C('DB_PREFIX').'sendonline'=>'so',C('DB_PREFIX').'sms'=>'sms'))->where($where)->count(); //总记录数
            $this->recordList($result);
            $this->display();
        }
    public function singleSend(){

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
