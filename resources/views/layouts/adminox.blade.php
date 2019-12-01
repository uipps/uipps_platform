<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <title>@yield('title')</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="/statics/assets/images/favicon.ico">


    <link href="/statics/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/statics/assets/css/core.css" rel="stylesheet">
    <link href="/statics/assets/css/components.css" rel="stylesheet">
    <link href="/statics/assets/css/icons.css" rel="stylesheet">
    <link href="/statics/assets/css/pages.css" rel="stylesheet">
    <link href="/statics/assets/css/menu.css" rel="stylesheet">
    <link href="/statics/assets/css/responsive.css" rel="stylesheet">
    <link href="/statics/plugins/c3/c3.min.css" rel="stylesheet">

    <style>
        .login-bg {
            background: url(/statics/assets/images/bg-login.jpg) no-repeat;
            background-size: cover;
        }
        .login-bg .container {
            background-color: rgba(33, 150, 243, 0.8);
        }
        .login-bg .container .account-box {
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
    </style>

</head>

<body class="bg-accpunt-pages login-bg">

<!-- Begin page -->
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="wrapper-page">
                <div class="account-pages">
                        @yield('content')
                </div>
            </div>
            <!-- end wrapper -->

        </div>
    </div>
</div>

<!-- jQuery  -->
<script type="text/javascript" src="/statics/assets/js/jquery.min.js"></script>
<script type="text/javascript" src="/statics/assets/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/statics/assets/js/metisMenu.min.js"></script>
<script type="text/javascript" src="/statics/assets/js/waves.js"></script>
<script type="text/javascript" src="/statics/assets/js/jquery.slimscroll.js"></script>


<!-- Counter js  -->
<script src="/statics/plugins/waypoints/jquery.waypoints.min.js"></script>
<script src="/statics/plugins/counterup/jquery.counterup.min.js"></script>

<!--C3 Chart-->
<script type="text/javascript" src="/statics/plugins/d3/d3.min.js"></script>
<script type="text/javascript" src="/statics/plugins/c3/c3.min.js"></script>

<!--Echart Chart-->
<script src="/statics/plugins/echart/echarts-all.js"></script>

<!-- Dashboard init -->
<script src="/statics/assets/pages/jquery.dashboard.js"></script>


<!-- App js -->
<script type="text/javascript" src="/statics/assets/js/jquery.core.js"></script>
<script type="text/javascript" src="/statics/assets/js/jquery.app.js"></script>


</body>
</html>
