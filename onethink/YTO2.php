<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jiabin
 * Date: 13-12-19
 * Time: 下午5:28
 * To change this template use File | Settings | File Templates.
 */
header('Content-Type:text/html;charset=utf-8');

//常量参数
define('DB_HOST','127.0.0.1:3306');
define('DB_USER','root');
define('DB_PWD','');
define('DB_NAME','wuliu');

//1.连接mysql服务器
$conn = mysql_connect(DB_HOST,DB_USER,DB_PWD) or die('mysql connected error cause: '.mysql_error());

//2.选择制定数据库设置字符集
mysql_select_db(DB_NAME) or die('db selected error cause: '.mysql_error());
mysql_query('SET NAMES UTF8') or die('set names error cause: '.mysql_error());
$total =mysql_fetch_array(@mysql_query("select count(*) as count from wuliu_sendonline where type=1 and company_id =3 and FROM_UNIXTIME(receive_time,'%Y-%m-%d')=curdate() and receive_time+6*3600<=unix_timestamp()"));
$count = $total["count"];
$info = date("Y-m-d H:i:s") . "  当前的记录数为 ".$count;
$fp = fopen('D:/YTO2_log.txt','a+');
//$fp = fopen('/home/wwwroot/default/onethink/yto2_log',"a+");
fwrite($fp, "-----------------------------------------------");
fwrite($fp, $info);
fwrite($fp, "----------------------------------------------- \r\n");
$result = mysql_query("select * from wuliu_sendonline where type=3 and company_id =3 and FROM_UNIXTIME(receive_time,'%Y-%m-%d')=curdate() and receive_time+3*3600<=unix_timestamp()");
while(!!$row = mysql_fetch_array($result)){
    $res=mysql_query("UPDATE wuliu_sendonline SET type=4 WHERE id=".$row["id"]);
    $info=$row["name"]."：入库时间为".date("Y-m-d H:i:s",$row["receive_time"])."的货物被设置为问题件，可以拨打".$row["tel"]."和他联系~ "."\r\n";
    fwrite($fp, $info);
}

