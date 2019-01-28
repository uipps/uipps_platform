# uipps_platform

## 关于UIPPS系统
UIPPS(universal information publish platform) 通用信息发布平台 基于laravel5.7版本，独特的设计理念

## 安装步骤
- git clone git@github.com:uipps/uipps_platform.git
- docker-compose up -d
- 复制.env.example为.env
- 配置.env里的数据库连接信息
- composer update
- php artisan migrate
- php artisan db:seed
- php artisan key:generate
- 登录后台：host/admin   帐号：admin  密码：admin

### docker的安装
- 参考官方文档 [windows](https://docs.docker.com/docker-for-windows/) , [mac](https://docs.docker.com/docker-for-mac/) 

