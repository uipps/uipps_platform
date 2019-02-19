<html>
<head>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<LINK REL="STYLESHEET" href="{{$RES_WEBPATH_PREF}}dpa_html/css/TreeMaker/main.css">
</head>
<body style="margin:5 0 0 0 ;background-color:#D0DCE0" oncontextmenu="return true" onselectstart="return false">
<table border="0" cellPadding="0" cellSpacing="0">
  <tr>
    <td width="10"></td>
    <td>
      用户：{{$nickname}}<BR>
      地址：{{$ip}}
    </td>
  </tr>
</table>
<script LANGUAGE="JavaScript" src="{{$RES_WEBPATH_PREF}}dpa_html/JSLib/treeMaker/tree_maker.js"></SCRIPT>
<script LANGUAGE="JavaScript" src="{{$RES_WEBPATH_PREF}}dpa_html/JSLib/treeMaker/tree_data.js"></SCRIPT>
<iframe height=0 width=0></iframe>
<script type="text/javascript">
  //-----------------------------------------------------
  // 节点展开事件函数
  // 只有按住Shift并左点击鼠标时才强制刷新
  //-----------------------------------------------------
  tree.callback_expanding = function my_expanding(event,nodeID)
  {
    if(event.shiftKey)
    {
      var cur_node = tree.getNode(nodeID);
      var cur_child_count = cur_node.childCount;
      if(cur_node.needrefresh != "undefined" && cur_node.needrefresh)
      {
        cur_node.loaded = false;
        for(var i=0; i<cur_child_count; i++)
        {
          cur_node.delChild(i);
        }
      }
    }
    return true;
  }
  //----------------------------------------------------------------------------------
  // 添加发布中心主菜单
  //----------------------------------------------------------------------------------
  var root_node = tree.add(0, Tree_ROOT, Tree_LAST, "项目中心", "","","","");

  //----------------------------------------------------------------------------------
  // 项目列表主菜单
  //----------------------------------------------------------------------------------
  var child_node = root_node.addChild(Tree_LAST,'项目列表');
  child_node.needrefresh = true;
  child_node.setScript('LoadProjListMenu(tree.getSelect().id)');
  child_node.addChild(Tree_LAST,'loading...');

  //----------------------------------------------------------------------------------
  // 项目管理菜单项
  //----------------------------------------------------------------------------------

  child_node = child_node.addSibling(Tree_LAST, '项目管理');
  child_node.setLink('main.php?do=project_list&pt=PUB', '');

  //----------------------------------------------------------------------------------
  //系统数据插件管理菜单项
  //----------------------------------------------------------------------------------

  //---------------------------------------------------------
  // 添加数据中心菜单
  //---------------------------------------------------------

  //---------------------------------------------------------
  // 添加数据采集菜单
  //---------------------------------------------------------
  //---------------------------------------------------------
  // 添加计划任务管理
  //---------------------------------------------------------
  var root_node = tree.add(0, Tree_ROOT, Tree_LAST, "计划任务管理", "","","","");
  var child_node = root_node.addChild(Tree_LAST, '计划任务');
  child_node.setLink('main.php?do=schedule_list', '');

  //---------------------------------------------------------
  // 添加系统管理菜单
  //---------------------------------------------------------
  root_node = root_node.addSibling(Tree_LAST, '系统管理');
  var child_node = root_node.addChild(0, '--------------');


  //----------------------------------------------------------------------------------
  //修改密码
  //----------------------------------------------------------------------------------
  child_node = child_node.addSibling(Tree_LAST, '修改密码');
  child_node.setLink('main.php?do=pasword_edit', '');

/*
  //权限管理
  child_node = child_node.addSibling(Tree_LAST, '权限管理');
  child_node.setLink('main.php?do=right_interface', '');
*/

  child_node = child_node.addSibling(Tree_LAST, '登录日志');
  child_node.setLink('main.php?do=loginlog_list', '');

  //---------------------------------------------------------
  // 添加帮助菜单
  //---------------------------------------------------------


  //删除
  root_node.delChild(0);

  //----------------------------------------------------------
  //退出发布系统
  //----------------------------------------------------------
  root_node = root_node.addSibling(Tree_LAST, "退出系统");
  root_node.setScript('QuitGSPS()');


</script>

<script>
  //---------------------------------------------------------
  //退出发布系统
  //---------------------------------------------------------
  function QuitGSPS()
  {
    if(confirm("您真的要退出系统?"))
    {

      window.open("main.php?do=logout","_top","");
      return true;
    }
    else
    {
      return false;
    }
  }
</script>

<script>
  //---------------------------------------------------------
  // 加载数据中心DataSpace列表
  // nodeID为点击结点的id
  //---------------------------------------------------------
  function LoadDataCenterMenu(nodeID)
  {
    var node = tree.getNode(nodeID);
    if( node && node.loaded != true )
    {
      node.delChild(0);//删除临时结点
      window.frames[0].location= "main.php?do=GetDataSpaceList&node=" + nodeID + "&menuid=" + node.menuid;
    }
  }

  //---------------------------------------------------------
  // 加载发布项目列表
  // nodeID为点击结点的id
  //---------------------------------------------------------
  function LoadProjListMenu(nodeID)
  {
    var node = tree.getNode(nodeID);
    if( node && node.loaded != true )
    {
      window.frames[0].location= "/admin/GetProjectListJS/pt/PUB/node/" + nodeID;
    }
  }

  //---------------------------------------------------------
  // 加载资源项目列表
  // nodeID为点击结点的id
  //---------------------------------------------------------
  function LoadResListMenu(nodeID)
  {
    var node = tree.getNode(nodeID);
    if( node && node.loaded != true )
    {
      window.frames[0].location= "/admin/GetProjectListJS/pt/RES/node/" + nodeID;
    }
  }

  //---------------------------------------------------------
  // 加载数据中心数据空间目列表
  // nodeID为点击结点的id
  //---------------------------------------------------------
  function LoadDataSpaceListMenu(nodeID)
  {
    var node = tree.getNode(nodeID);
    if( node && node.loaded != true )
    {
      window.frames[0].location= "gdms/main.php?do=GetDataSpaceListJS&pt=DATASPACE&node=" + nodeID;
    }
  }

  //---------------------------------------------------------
  // 加载发布项目模板列表
  // nodeID为点击结点的id
  //---------------------------------------------------------
  function LoadTemplateListMenu(nodeID, p_id)
  {
    var node = tree.getNode(nodeID);
    if( node && node.loaded != true )
    {
      window.frames[0].location= "main.php?do=GetTemplateListJS&node=" + nodeID + "&p_id=" + p_id;
    }
  }

  //---------------------------------------------------------
  // 加载Spider子菜单
  // nodeID为点击结点的id
  //---------------------------------------------------------
  function LoadSpiderMenu()
  {
    var win;
    win=self.open("main.php?do=spider_login","_blank", 'location=no, menubar=no');
    win.moveTo(0,0);
    win.resizeTo(screen.width,screen.height);
    win.focus();
    return;
    //var node = tree.getNode(nodeID);
    //if( node && node.loaded != true )
    //{
      //node.delChild(0);//删除临时结点
    //  window.frames[0].location= "main.php?do=spider_login" + nodeID + "&menuid=" + node.menuid;
    //}
  }


  //----------------------
  //加载统一分类层次
  //----------------------
  function LoadCategoryTreeMenu(nodeID,gdid,c_pid)
  {
    var node = tree.getNode(nodeID);
    if( node && node.loaded != true )
    {
      window.frames[0].location= "main.php?do=gdms_category_tree_list&gdid=" + gdid + "&node=" + nodeID + "&c_pid=" + c_pid;
    }
  }

  //----------------------
  //加载专一分类层次
  //----------------------
  function LoadDetailCategoryTreeMenu(nodeID,gdid,dc_pid)
  {
    var node = tree.getNode(nodeID);
    if( node && node.loaded != true )
    {
      window.frames[0].location= "main.php?do=gdms_detailcategory_tree_list&gdid=" + gdid + "&node=" + nodeID + "&dc_pid=" + dc_pid;
    }
  }

  //---------------------------------------------------------
  // 处理节点点击事件
  // nodeID为点击结点的id
  //---------------------------------------------------------
  tree.callback_click = function my_click(nodeID)
  {
    var node=tree.getNode(nodeID);
    if(node.childCount>0 && node.expanded==true
      || node.childCount==0)
      return true;
    return false;
  }


  //---------------------------------------------------------
  // 默认第一个菜单选中
  //---------------------------------------------------------
  tree.getRoot().click();
  tree.getRoot().child[1].select();


</script>

</body>
</html>
<!-- CHT 2009-03-21 19:07:13 -->

