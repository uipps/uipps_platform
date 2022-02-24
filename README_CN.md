UIPPS(universal information publish platform) 通用信息发布平台 基于laravel5.7版本，独特的设计理念，颠覆传统开发方式，一个后台管理搞定API、H5；集中管理高效开发。
也叫IPPFI(intelligent publish platform for information ) 智能信息发布平台 。

# 目录
- [1.安装步骤](#1安装步骤)
- [2.编码约束](#2编码约束-目录结构约定)
- [3.大公司架构](#3题外话：大型公司软件技术架构)
- [4.常见问题](#4常见问题)
- [5.附录](#5附录)


## 1.安装步骤
- `git clone git@github.com:uipps/uipps_platform.git`
- `docker-compose up -d` (没有docker可不执行)
- 复制.env.example为.env `cp .env.example .env`
- 修改.env文件里的数据库连接、邮箱等信息
- `composer update` (更换国内镜像或代理free_proxy composer update)
- `php artisan key:generate` ( 可不用执行，已经在env中手动指定APP_KEY了)
- `php artisan make:auth`    ( 可不用执行，因生成路由、controller、blade模版等文件)
- `php artisan migrate:fresh`
- `php artisan db:seed --class=project`
- `php artisan initproject InitTableField`
- 登录后台：http://host.uipps.com/admin   帐号：admin  密码：admin


## 2.编码约束-目录结构约定
- Model层，只做一个表的声明，如：连哪个库、哪个表等
- Repositories层，可以引入多个表model，编写数据库表以及redis等。
  > 只能调用model层，不能调用其它Repositories
- Service层，业务模块服务，一个service编写一个模块的业务逻辑。
  > 只能调用Repositories层，不能调用其它service和model层
- Logic层，公共业务逻辑
  > 只能由Controller调用
- 层级关系 Controller->Logic->service->repository->model
  > 下层不能调用上层，同层不能互相调用
  

## 3.题外话：大型公司软件技术架构
- 用openfire搭建公司内部聊天系统服务器，员工下载spark/pidgin即可使用；防止微信、QQ窃听公司内部聊天机密 
- 搭建LDAP服务，一个账号公司各个系统都可以登录 （不安装ldap提供一个内部账号密码验证接口也一样）
- 搭建gitlab-用于内网代码管理，代码不用托管在github或码云等公网
- 搭建jira、wiki（用confluence），大公司都用jira做各种流程管理，wiki，bug处理等等
- 生产环境服务器权限不能随意给员工；
- 代码发布上线用gopub进行管理
- 另外搭建tfs（Taobao File System），或其他文件系统（七牛云等）
- 内网域名服务器，各种内部域名（优先程度次之）
- 用户首先访问的就是CDN, 顺序如下:  client  ->  CDN -> F5 -> nginx -> apache/php 
- 要开除人，设置账号过期或删除账号即可，不受员工要挟（删除代码等破坏活动）

- 关于代码规范：是否codereview视情况而定； 编辑器设置统一，如每行末尾空白自动去掉； .gitattributes设置统一，autocrlf = false，safecrlf = true ；

## 4.常见问题
1. 419 unknown status
  > 模板文件中增加 `<meta name="csrf-token" content="{{csrf_token()}}">` form表单增加 `{{csrf_field()}}`


## 5.附录
### 1. md文件编写 [markdown](https://www.appinn.com/markdown/) 
### 2. docker的安装, 官方文档 [windows](https://docs.docker.com/docker-for-windows/) , [mac](https://docs.docker.com/docker-for-mac/)
### 3. License : MIT

