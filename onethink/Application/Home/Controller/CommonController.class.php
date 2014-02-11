<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jiabin
 * Date: 13-12-10
 * Time: 下午5:26
 * To change this template use File | Settings | File Templates.
 */

namespace Home\Controller;
use Think\Controller;

/**
 * Class CommonController
 * @package Home\Controller
 * 公共接口调用
 */
class CommonController extends Controller{
    public function index() {
       $this->check();
       $method=$_REQUEST["method"];
        $this->$method();
        exit();
    }
    //检测请求是否合法
    private function check(){
        $method_array=array('getCompany');
        $method = trim($_REQUEST["method"]);
        $sign = trim($_REQUEST["sign"]);
        //签名方式
        $key='wuliu2013';	//双方约定的一个key
        $my_sign=md5($key);
        if(!in_array($method, $method_array)){
            $result["status"] = 0;
            $result["info"] = "请求的方法不存在";
            echo json_encode($result);
            exit;
        }
        if($my_sign!=$sign){
            $result["status"] = 0;
            $result["info"] = "签名不正确";
            echo json_encode($result);
            exit;
        }
        return true;
    }
    public function getCompany(){
        $wuliu = M("Express");
        $result = $wuliu->select();
        if($result){
            echo $list='{"Rows":'.json_encode($result).',status:1}';
        }else{
            echo $list='{status:0}';
        }
    }
    /**
     * 获取服务器端保存的手机版本号
     */
    public function getVersion(){
        $ver = M("Version");
        $result = $ver->order("id desc")->find();
        if($result){
            $result["status"]=1;
            //echo $list='{"Rows":'.json_encode($result).',status:1}';
            echo json_encode($result);
        }else{
            echo $list='{status:0}';
        }
    }
}