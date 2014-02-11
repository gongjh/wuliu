<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo ($meta_title); ?>|物流管理平台</title>
    <link href="/onethink/Public/favicon.ico" type="image/x-icon" rel="shortcut icon">
    <link rel="stylesheet" type="text/css" href="/onethink/Public/Admin/css/base.css" media="all">
    <link rel="stylesheet" type="text/css" href="/onethink/Public/Admin/css/common.css" media="all">
    <link rel="stylesheet" type="text/css" href="/onethink/Public/Admin/css/module.css">
    <link rel="stylesheet" type="text/css" href="/onethink/Public/Admin/css/style.css" media="all">
	<link rel="stylesheet" type="text/css" href="/onethink/Public/Admin/css/<?php echo (C("COLOR_STYLE")); ?>.css" media="all">
    <link href="/onethink/Public/static/thinkbox/ThinkBox.css" >
    <link href="/onethink/Public/static/bootstrap/css/bootstrap.min.css" >
    <script href="/onethink/Public/static/bootstrap/js/bootstrap.min.js" ></script>
    <script href="/onethink/Public/static/bootstrap/js/html5shiv.js" ></script>
     <!--[if lt IE 9]>
    <script type="text/javascript" src="/onethink/Public/static/jquery-1.10.2.min.js"></script>
    <![endif]--><!--[if gte IE 9]><!-->
    <script type="text/javascript" src="/onethink/Public/static/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="/onethink/Public/Admin/js/jquery.mousewheel.js"></script>
    <!--<![endif]-->
    <script href="/onethink/Public/static/jquery-1.7.2.min.js" ></script>
    <script href="/onethink/Public/static/thinkbox/jquery.ThinkBox.min.js" ></script>
    
</head>
<body>
    <!-- 头部 -->
    <?php $__base_menu__ = $__controller__->getMenus(); ?>
    <div class="header">
        <!-- Logo -->
        <!--<span class="logo"></span>-->
        <!-- /Logo -->

        <!-- 主导航 -->
        <ul class="main-nav">
            <li class=""><a href="#">物流管理</a></li>
            <?php if(is_array($__base_menu__["main"])): $i = 0; $__LIST__ = $__base_menu__["main"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li class="<?php echo ((isset($menu["class"]) && ($menu["class"] !== ""))?($menu["class"]):''); ?>"><a href="<?php echo (u($menu["url"])); ?>"><?php echo ($menu["title"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
        <!-- /主导航 -->

        <!-- 用户栏 -->
        <div class="user-bar">
            <a href="javascript:;" class="user-entrance"><i class="icon-user"></i></a>
            <ul class="nav-list user-menu hidden">
                <li class="manager">你好，<em title="<?php echo session('user_auth.username');?>"><?php echo session('user_auth.username');?></em></li>
                <li><a href="<?php echo U('User/updatePassword');?>">修改密码</a></li>
                <li><a href="<?php echo U('User/updateNickname');?>">修改昵称</a></li>
                <li><a href="<?php echo U('Public/logout');?>">退出</a></li>
            </ul>
        </div>
    </div>
    <!-- /头部 -->

    <!-- 边栏 -->
    <div class="sidebar">
        <!-- 子导航 -->
        
    <div id="subnav" class="subnav">
    <?php if(!empty($_extra_menu)): ?>
        <?php echo extra_menu($_extra_menu,$__base_menu__); endif; ?>
    <?php if(is_array($__base_menu__["child"])): $i = 0; $__LIST__ = $__base_menu__["child"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub_menu): $mod = ($i % 2 );++$i;?><!-- 子导航 -->
        <?php if(!empty($sub_menu)): if(!empty($key)): ?><h3><i class="icon icon-unfold"></i><?php echo ($key); ?></h3><?php endif; ?>
            <ul class="side-sub-menu">
                <?php if(is_array($sub_menu)): $i = 0; $__LIST__ = $sub_menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li>
                        <a class="item" href="<?php echo (u($menu["url"])); ?>"><?php echo ($menu["title"]); ?></a>
                    </li><?php endforeach; endif; else: echo "" ;endif; ?>
            </ul><?php endif; ?>
        <!-- /子导航 --><?php endforeach; endif; else: echo "" ;endif; ?>
    <?php if(is_array($nodes)): $i = 0; $__LIST__ = $nodes;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub_menu): $mod = ($i % 2 );++$i;?><!-- 子导航 -->
        <?php if(!empty($sub_menu)): ?><h3>
            	<i class="icon <?php if(($sub_menu['current']) != "1"): ?>icon-fold<?php endif; ?>"></i>
            	<?php if(($sub_menu['allow_publish']) > "0"): ?><a class="item" href="<?php echo (u($sub_menu["url"])); ?>"><?php echo ($sub_menu["title"]); ?></a><?php else: echo ($sub_menu["title"]); endif; ?>
            </h3>
            <ul class="side-sub-menu <?php if(($sub_menu["current"]) != "1"): ?>subnav-off<?php endif; ?>">
                <?php if(is_array($sub_menu['_child'])): $i = 0; $__LIST__ = $sub_menu['_child'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li <?php if($menu['id'] == $cate_id or $menu['current'] == 1): ?>class="current"<?php endif; ?>>
                        <?php if(($menu['allow_publish']) > "0"): ?><a class="item" href="<?php echo (u($menu["url"])); ?>"><?php echo ($menu["title"]); ?></a><?php else: echo ($menu["title"]); endif; ?>

                        <!-- 一级子菜单 -->
                        <?php if(isset($menu['_child'])): ?><ul class="subitem">
                        	<?php if(is_array($menu['_child'])): foreach($menu['_child'] as $key=>$three_menu): ?><li>
                                <?php if(($three_menu['allow_publish']) > "0"): ?><a class="item" href="<?php echo (u($three_menu["url"])); ?>"><?php echo ($three_menu["title"]); ?></a><?php else: echo ($three_menu["title"]); endif; ?>
                                <!-- 二级子菜单 -->
                                <?php if(isset($three_menu['_child'])): ?><ul class="subitem">
                                	<?php if(is_array($three_menu['_child'])): foreach($three_menu['_child'] as $key=>$four_menu): ?><li>
                                        <?php if(($four_menu['allow_publish']) > "0"): ?><a class="item" href="<?php echo U('index','cate_id='.$four_menu['id']);?>"><?php echo ($four_menu["title"]); ?></a><?php else: echo ($four_menu["title"]); endif; ?>
                                        <!-- 三级子菜单 -->
                                        <?php if(isset($four_menu['_child'])): ?><ul class="subitem">
                                        	<?php if(is_array($four_menu['_child'])): $i = 0; $__LIST__ = $four_menu['_child'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$five_menu): $mod = ($i % 2 );++$i;?><li>
                                            	<?php if(($five_menu['allow_publish']) > "0"): ?><a class="item" href="<?php echo U('index','cate_id='.$five_menu['id']);?>"><?php echo ($five_menu["title"]); ?></a><?php else: echo ($five_menu["title"]); endif; ?>
                                            </li><?php endforeach; endif; else: echo "" ;endif; ?>
                                        </ul><?php endif; ?>
                                        <!-- end 三级子菜单 -->
                                    </li><?php endforeach; endif; ?>
                                </ul><?php endif; ?>
                                <!-- end 二级子菜单 -->
                            </li><?php endforeach; endif; ?>
                        </ul><?php endif; ?>
                        <!-- end 一级子菜单 -->
                    </li><?php endforeach; endif; else: echo "" ;endif; ?>
            </ul><?php endif; ?>
        <!-- /子导航 --><?php endforeach; endif; else: echo "" ;endif; ?>
</div>
<script>
    $(function(){
        $(".side-sub-menu li").hover(function(){
            $(this).addClass("hover");
        },function(){
            $(this).removeClass("hover");
        });
    })
</script>

   

        <!-- /子导航 -->
    </div>
    <!-- /边栏 -->

    <!-- 内容区 -->
    <div id="main-content">
        <div id="top-alert" class="fixed alert alert-error" style="display: none;">
            <button class="close fixed" style="margin-top: 4px;">&times;</button>
            <div class="alert-content">这是内容</div>
        </div>
        <div id="main" class="main">
            
            <!-- nav -->
            <?php if(!empty($_show_nav)): ?><div class="breadcrumb">
                <span>您的位置:</span>
                <?php $i = '1'; ?>
                <?php if(is_array($_nav)): foreach($_nav as $k=>$v): if($i == count($_nav)): ?><span><?php echo ($v); ?></span>
                    <?php else: ?>
                    <span><a href="<?php echo ($k); ?>"><?php echo ($v); ?></a>&gt;</span><?php endif; ?>
                    <?php $i = $i+1; endforeach; endif; ?>
            </div><?php endif; ?>
            <!-- nav -->
            

            
	<!-- 标题栏 -->
	<div class="main-title">
		<h2>即时发货列表</h2>
	</div>
	<div class="cf">
		<div class="fl">
            <a class="btn" href="<?php echo U('Sendonline/add');?>">新 增</a>
            <button class="btn ajax-post confirm" target-form="ids" url="<?php echo U('del');?>">删 除</button>
            <a class="btn" href="<?php echo U('Sendonline/export');?>">导出</a>
            <button class="btn ajax-post confirm" target-form="ids" url="<?php echo U('put');?>">取货</button>
            <button class="btn ajax-post confirm" target-form="ids" url="<?php echo U('back');?>">遣返</button>
        </div>
</div>
    <div class="cf">
        <!-- 高级搜索 -->
        <form action="<?php echo U('Sendonline/index');?>" method="POST">
        <div class=".search-form f1">
               <div class="sleft">
                   类型：<select name="type" id="type">
                   <option value=""> 所有</option>
                   <option value="1"> 普通</option>
                   <option value="2"> 同城</option>
                   <option value="3"> 加急</option>
                   <option value="4">遗留</option>
               </select>
                   &nbsp; &nbsp; &nbsp;
                   物流公司：
                   <select name="company_id">
                       <option value="">所有</option>
                       <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                   </select><br>
                   开始时间： <input type="text" id="firstDate"  name="firstDate" class="text  time" value="<?php echo ($firstDate); ?>" placeholder="开始时间">
                   结束时间： <input type="text" id="endDate" name="endDate" class="text  time" value="<?php echo ($endDate); ?>" placeholder="结束时间">
                   <br>
                   <input type="text" class="text input-large" name="condition" placeholder="手机尾号/姓名/货架号/快递单号" value="<?php echo ($code); ?>">
                   <input type="submit" value="搜索"  id="submit" class="btn">
               </div>
        </div></form>
    </div>
    <!-- 数据列表 -->
    <div class="data-table table-striped">
	<table class="table table-bordered">
    <thead>
        <tr>
		<th class="row-selected row-selected"><input class="check-all" type="checkbox"/></th>
		<!--<th class="">ID</th>-->
		<th class="">收货人姓名</th>
		<th class="">快递单号</th>
		<th class="">电话</th>
         <th class="">物流公司名称</th>
         <th class="">货架号</th>
		<th class="">入库时间</th>
		<th class="">所在站点</th>
         <th class="">类型</th>
         <th class="">操作</th>
		</tr>
    </thead>
    <tbody>
		<?php if(is_array($_list)): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr style="background-color: #ffffff;">
            <td><input class="ids" type="checkbox" name="ids[]" value="<?php echo ($vo["id"]); ?>" />
            </td>
			<!--<td><?php echo ($vo["id"]); ?> </td>-->
			<td><?php echo ($vo["name"]); ?></td>
			<td><?php echo ($vo["code"]); ?></td>
			<td><?php echo ($vo["tel"]); ?></td>
			<td><span><?php echo (getcompany($vo["company_id"])); ?></span></td>
			<td><?php echo ($vo["shelf"]); ?></td>
            <td><span><?php echo (time_format($vo["receive_time"])); ?></span></td>
            <td><span><?php echo ($vo["city"]); ?></span></td>
            <td><?php echo (get_type($vo["type"])); ?>
            </td>
            <td ><a class="btn" href="<?php echo U('Sendonline/edit','id='.$vo['id']);?>">编辑</a>
                <a class="btn" href="<?php echo U('Sendonline/remark','id='.$vo['id']);?>">备注</a>
            </td>
		</tr>
            <tr style="background-color: #ffffff;">
                <td colspan="10" style=" ">备注：<?php echo ($vo["remark"]); ?></td>
            </tr>
            <tr style="background-color: #ffffff;">
                <td colspan="10" style=" "><img src="/onethink/Public/Admin/images/left.png" style="float: left;margin-left:-20px;width: 50%" ><img src="/onethink/Public/Admin/images/right.png"  style="margin-right:-20px;float: right;width: 50%"></td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
	</tbody>
    </table> 
	</div>
    <div class="page">
        <?php echo ($_page); ?>
    </div>

        </div>
        <div class="cont-ft">
            <div class="copyright">
                <div class="fl">感谢使用<a href="#" target="_blank">空气动力</a>物流管理平台</div>

            </div>
        </div>
    </div>
    <!-- /内容区 -->
    <script type="text/javascript">
    (function(){
        var ThinkPHP = window.Think = {
            "ROOT"   : "/onethink", //当前网站地址
            "APP"    : "/onethink/index.php?s=", //当前项目地址
            "PUBLIC" : "/onethink/Public", //项目公共目录地址
            "DEEP"   : "<?php echo C('URL_PATHINFO_DEPR');?>", //PATHINFO分割符
            "MODEL"  : ["<?php echo C('URL_MODEL');?>", "<?php echo C('URL_CASE_INSENSITIVE');?>", "<?php echo C('URL_HTML_SUFFIX');?>"],
            "VAR"    : ["<?php echo C('VAR_MODULE');?>", "<?php echo C('VAR_CONTROLLER');?>", "<?php echo C('VAR_ACTION');?>"]
        }
    })();
    </script>
    <script type="text/javascript" src="/onethink/Public/static/think.js"></script>
    <script type="text/javascript" src="/onethink/Public/Admin/js/common.js"></script>
    <script type="text/javascript">
        +function(){
            var $window = $(window), $subnav = $("#subnav"), url;
            $window.resize(function(){
                $("#main").css("min-height", $window.height() - 130);
            }).resize();

            /* 左边菜单高亮 */
            url = window.location.pathname + window.location.search;
            url = url.replace(".html", "")
                .replace(/(\/(p)\/\d+)|(&p=\d+)|(\/(id)\/\d+)|(&id=\d+)/, "");
            $subnav.find("a[href^='" + url + "']").parent().addClass("current");

            /* 左边菜单显示收起 */
            $("#subnav").on("click", "h3", function(){
                var $this = $(this);
                $this.find(".icon").toggleClass("icon-fold");
                $this.next().slideToggle("fast").siblings(".side-sub-menu:visible").
                      prev("h3").find("i").addClass("icon-fold").end().end().hide();
            });

            $("#subnav h3 a").click(function(e){e.stopPropagation()});

            /* 头部管理员菜单 */
            $(".user-bar").mouseenter(function(){
                var userMenu = $(this).children(".user-menu ");
                userMenu.removeClass("hidden");
                clearTimeout(userMenu.data("timeout"));
            }).mouseleave(function(){
                var userMenu = $(this).children(".user-menu");
                userMenu.data("timeout") && clearTimeout(userMenu.data("timeout"));
                userMenu.data("timeout", setTimeout(function(){userMenu.addClass("hidden")}, 100));
            });

	        /* 表单获取焦点变色 */
	        $("form").on("focus", "input", function(){
		        $(this).addClass('focus');
	        }).on("blur","input",function(){
				        $(this).removeClass('focus');
			        });
		    $("form").on("focus", "textarea", function(){
			    $(this).closest('label').addClass('focus');
		    }).on("blur","textarea",function(){
			    $(this).closest('label').removeClass('focus');
		    });

            // 导航栏超出窗口高度后的模拟滚动条
            var sHeight = $(".sidebar").height();
            var subHeight  = $(".subnav").height();
            var diff = subHeight - sHeight; //250
            var sub = $(".subnav");
            if(diff > 0){
                $(window).mousewheel(function(event, delta){
                    if(delta>0){
                        if(parseInt(sub.css('marginTop'))>-10){
                            sub.css('marginTop','0px');
                        }else{
                            sub.css('marginTop','+='+10);
                        }
                    }else{
                        if(parseInt(sub.css('marginTop'))<'-'+(diff-10)){
                            sub.css('marginTop','-'+(diff-10));
                        }else{
                            sub.css('marginTop','-='+10);
                        }
                    }
                });
            }
        }();
    </script>
    
	<script src="/onethink/Public/static/thinkbox/jquery.thinkbox.js"></script>
    <link href="/onethink/Public/static/datetimepicker/css/datetimepicker.css" rel="stylesheet" type="text/css">
    <?php if(C('COLOR_STYLE')=='blue_color') echo '<link href="/onethink/Public/static/datetimepicker/css/datetimepicker_blue.css" rel="stylesheet" type="text/css">'; ?>
    <link href="/onethink/Public/static/datetimepicker/css/dropdown.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/onethink/Public/static/datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <script type="text/javascript" src="/onethink/Public/static/datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js" charset="UTF-8"></script>
	<script type="text/javascript">
        $(function(){
            $('.time').datetimepicker({
                format: 'yyyy-mm-dd',
                language:"zh-CN",
                minView:2,
                autoclose:true
            });
            showTab();
            $("#update_type").change(function(){
                $.post("<?php echo U('Public/update_type');?>", {type:$("#update_type").val(),id:$("#update_type").attr("data-group") },
                        function(data){
                           // $("#price").val(data);
                        });
            });

        })
	</script>

</body>
</html>