<?php
/**
 * Created by JetBrains PhpStorm.
 * User: taotao
 * Date: 13-12-8
 * Time: 下午11:40
 * To change this template use File | Settings | File Templates.
 */

namespace Admin\Model;
use Think\Model;

/**
 * 导航模型
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */

class SendonlineModel extends Model {
    protected $_validate = array(
        //array('name', 'require', '收货人姓名不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('code', 'require', '快递单号不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
        array('code','','快递单号已经存在！',0,'unique',1),
      //  array('code', '', '快递单号已经存在', self::VALUE_VALIDATE, self::MODEL_INSERT),
        array('city', 'require', '所在城市不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
        array('tel', 'require', '电话不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('receive_time', NOW_TIME, self::MODEL_INSERT),
    );

}