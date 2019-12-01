@extends('layouts.adminox')

@section('title', 'login-page')

@section('content')
    <script type="text/javascript">
        <!--//
        String.prototype.trim=function(){return this.replace(/(^\s*)|(\s*$)/g,"")};
        if(typeof $=='undefined')$=function(id){return document.getElementById(id)};
        function con_code()
        {
            var qq= Math.round((Math.random()) * 100000000);
            $("check_img").src = '/common/lib/aipic.php?create=yes&r=' + qq;
            $("check_img").style.display = "block";
            $("imgcode").value = qq;
        }
        function  yanzhen(a_obj,a_type,a_must){
            var err = false;
            var old_style_border = a_obj.style.border;
            var val = a_obj.value.trim();

            if ((val == '' || val == '-' || val == 'http://') && a_must == true) err = true;

            if (err == false && val != '')
            {
                if (a_type == 'url' &&
                    val.substr(0,7) != 'http://' &&
                    val.substr(0,8) != 'https://')
                    err = true;

                if (a_type == 'email' &&
                    (val.indexOf('@') < 1 || val.indexOf('@') == (val.length - 1)))
                    err = true;

                if (a_type == 'int+0' && (isNaN(val) || parseInt(val) <= 0))
                    err = true;

                if (a_type == 'float' && (isNaN(val) || parseFloat(val) <= 0) )
                {
                    err = true;
                }
            }


            // Change class
            if (err) a_obj.style.borderColor = "#990000";
            else a_obj.style.borderColor = old_style_border;//"#D2D2D2";

            return (err);

        }

        function chkform(a_obj)
        {
            //var frm = document.forms[num];
            var frm = a_obj;
            var errnum = 0;
            var errsrt = "对不起，有以下问题需要您更正\n\n";


            if( "" == frm["username"].value ){
                errnum++;
                errsrt += "- “用户名”是必填项！\n";
            }
            if( "" == frm["password"].value ){
                errnum++;
                errsrt += "- “密码”是必填项！ \n";
            }
            {{$l_yanzhengma_js}}

            if (errnum>0) {
                alert(errsrt+"\n\n多谢您的支持 :D");
                //return false;
            } else{
                frm.submit();
                //return true;
            }
            return false
        }
        //-->
    </script>
    <div class="account-box">
        <div class="account-logo-box">
            <h3 class="text-uppercase font-bold m-b-5 m-t-50">{{$system_name}}登录</h3>
        </div>
        <div class="account-content">
            <form class="form-horizontal" name="loginform" id="loginform" method="post" action="" onsubmit="return chkform(this)" enctype="multipart/form-data">
                <input type="hidden" name="r" value="main">
                <input type="hidden" name="back_url" value="{{$back_url}}" />
                {{csrf_field()}}
                <div class="form-group m-b-20">
                    <div class="col-xs-12">
                        <span style="color: #FF0000">{{$action_error_notice}}</span>
                    </div>
                </div>
                <div class="form-group m-b-20">
                    <div class="col-xs-12">
                        <label for="username">用户名</label>
                        <input class="form-control" type="text" name="username" id="username" required="" placeholder="john@deo.com" value="">
                    </div>
                </div>

                <div class="form-group m-b-20">
                    <div class="col-xs-12">
                        <label for="password">密码</label>
                        <input class="form-control" type="password" name="password" required="" id="password" placeholder="Enter your password" value="">
                        <!--<a href="#" class="text-muted pull-right"><small>忘记密码?</small></a>-->
                    </div>
                </div>

                <div class="form-group m-b-20">
                    <div class="col-xs-12">
                        <label for="googlecode">谷歌验证</label>
                        <input class="form-control" type="password" name="googlecode" id="googlecode" placeholder="Enter google auth" value="">
                    </div>
                </div>
                {{$l_yanzhengma}}
                <div class="form-group m-b-20">
                    <div class="col-xs-12">
                        <div class="checkbox checkbox-success">
                            <input id="remember" type="checkbox" checked="">
                            <label for="remember">
                                记住
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center m-t-10">
                    <div class="col-xs-12">
                        <button class="btn btn-md btn-block btn-primary waves-effect waves-light" type="submit">登录</button>
                    </div>
                </div>

            </form>
            <div class="row m-t-50">
                <div class="col-sm-12 text-center">
                    <p class="text-muted">还没有账户? <a href="#" class="text-dark m-l-5"><b>注册</b></a></p>
                </div>
            </div>

        </div>
    </div>
@endsection
