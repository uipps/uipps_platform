# uipps_platform

## 关于UIPPS系统
UIPPS(universal information publish platform) 通用信息发布平台 基于laravel5.7版本，独特的设计理念

## 安装步骤
- `git clone git@github.com:uipps/uipps_platform.git`
- `docker-compose up -d`
- 复制.env.example为.env `cp .env.example .env`
- 配置.env里的数据库连接信息
- `composer update`
- `php artisan make:auth`
- `php artisan migrate`
- `php artisan db:seed`
- `php artisan key:generate`
- 登录后台：http://host.uipps.com/admin   帐号：admin  密码：admin


## 编码约束-目录结构约定
- Model层，只做一个表的声明，如：连哪个库、哪个表等
- Repositories层，可以引入多个表model，编写数据库表以及redis等。
  > 只能调用model层，不能调用其它Repositories
- Service层，业务模块服务，一个service编写一个模块的业务逻辑。
  > 只能调用Repositories层，不能调用其它service和model层
- Logic层，公共业务逻辑
  > 只能由Controller调用
- 层级关系 Controller->Logic->service->repository->model
  > 下层不能调用上层，同层不能互相调用
  
## 小型公司技术架构
- 搭建LDAP服务，一个账号公司各个系统都可以登录
- 搭建gitlab-用于内网代码管理，代码不用托管在github或码云等公网
- 搭建jira、wiki（用confluence），大公司都用jira做各种流程管理，wiki，bug处理等等
- 内网域名服务器，各种内部域名（优先程度次之）
- 要开除人，设置账号过期或删除账号即可，不用担心其删除代码等破坏活动

## 附录
### 1. md文件编写 [markdown](https://www.appinn.com/markdown/) 
### 2. docker的安装, 官方文档 [windows](https://docs.docker.com/docker-for-windows/) , [mac](https://docs.docker.com/docker-for-mac/)


