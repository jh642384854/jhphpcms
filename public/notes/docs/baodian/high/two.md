---
title: Swoft CLI
lang: zh-cn
description: SwoftcLI描述内容
date: 2020-07-24 17:57:54
comments: 1
search: 1
tags:

categories:
 - PHP
 - 框架手册
 - Swoft
 - 开发工具

---
# 下载安装
## 简介
GitHub: [https://github.com/swoft-cloud/swoft-cli](https://github.com/swoft-cloud/swoft-cli "https://github.com/swoft-cloud/swoft-cli")

Swoft CLI 是一个独立的命令行应用，提供了一些内置的功能方便开发者使用：

- 生成 Swoft 应用类文件，例如：HTTP 控制器，WebSocket 模块类等
- 监视用户 Swoft 项目的文件更改并自动重新启动服务器
- 快速创建新应用或组件
- 将一个 Swoft 应用打包成 Phar 包
欢迎提供意见、贡献代码

> Swoft CLI 是基于 Swoft 2.0 框架构建的应用，运行时同样需要安装 Swoole

## 运行预览
    $ php swoftcli.phar -h
    
    🛠️ Command line tool application for quick use swoft (Version:  0.1.3)
    
    Usage:
      swoftcli.phar COMMAND [arg0 arg1 arg2 ...] [--opt -v -h ...]
    
    Options:
          --debug      Setting the application runtime debug level(0 - 4)
          --no-color   Disable color/ANSI for message output
      -h, --help       Display this help message
      -V, --version    Show application version information
          --expand     Expand sub-commands for all command groups
    
    Available Commands:
      client         Provide some commands for quick connect tcp, ws server
      gen            Generate some common application template classes(alias: generate)
      new            Provide some commads for quick create new application or component(alias: create)
      phar           There are some command for help package application
      self-update    Update the swoft-cli to latest version from github(alias: selfupdate, update-self, updateself)
      serve          Provide some commands for manage and watch swoft server project
      system         Provide some system information commands[WIP](alias: sys)
      tool           Some internal tool commands, like ab, update-self
    
    More command information, please use: swoftcli.phar COMMAND -h

## 安装
安装 Swoft CLI 非常简单，我们已经提供打包好的 Phar 放在 GitHub 上，只需从 Swoft CLI Releases - GitHub 下载打包好的 swoftcli.phar 文件即可。当然你也可以通过 wget 命令下载：

wget https://github.com/swoft-cloud/swoft-cli/releases/download/{VERSION}/swoftcli.phar
> 注意：你需要替换 {VERSION} 部分为最新版本。

# 检查包是否可用，打印版本信息
`php swoftcli.phar -V`

# 显示帮助信息
`php swoftcli.phar -h`
## 全局使用
如果你需要在任何地方都可以直接使用 Swoft CLI：

    mv swoftcli.phar /usr/local/bin/swoftcli && chmod a+x /usr/local/bin/swoftcli
    
    # 完成后检查是否可用
    swoftcli -V
## 手动构建
如果你需要通过最新的 Swoft CLI 或修改后的代码构建 Phar 包：

    git clone https://github.com/swoft-cloud/swoft-cli
    cd swoft-cli
    composer install
    
    # 构建
    php -d phar.readonly=0 ./bin/swoftcli phar:pack -o=swoftcli.phar

# 自动重启服务
Swoft2.0 在内置组件中去除了自动重启功能，由 Swoft-cli 来提供。帮助开发者在开发时能让改动的代码快速生效。

> 需要注意：2.0 里面重启的是整个服务而不是像1.0一样只 reload 工作进程

运行命令：serve:run
## 查看可用选项
    php swoftcli.phar run -h
    # 如果已经放到了全局PATH里，可用这样使用
    swoftcli run -h
运行结果如下：
    $ php swoftcli.phar run -h
    
    Start the swoft server and monitor the file changes to restart the server
    
    Usage:
      swoftcli.phar run [arguments ...] [options ...]
    
    Global Options:
          --debug      Setting the application runtime debug level(0 - 4)
          --no-color   Disable color/ANSI for message output
      -h, --help       Display help message for application or command
      -V, --version    Display application version information
    
    Arguments:
      targetPath PATH   Your swoft project path, default is current work directory
    
    Options:
      -b, --bin-file STRING       Entry file for the swoft project (defaults: bin/swoft)
          --interval INTEGER      Interval time for watch files, unit is seconds (defaults: 3)
          --php-bin STRING        Custom the php bin file path (defaults: php)
      -c, --start-cmd STRING      The server startup command to be executed (defaults: http:start)
      -w, --watch DIRECTORIES     List of directories you want to watch, relative the targetPath (defaults: app,config)
    
    Example:
      swoftcli.phar run    Default, will start http server
      swoftcli.phar run -c ws:start -b bin/swoft /path/to/swoft
## 参数列表
- targetPath 指定要运行的Swoft应用所在目录，默认为当前目录
## 选项列表
- -b, --bin-flie 指定Swoft应用的入口文件，默认是bin/swoft
- --interval 监控文件的间隔时间，默认3s检查一次
- --php-bin 指定你的php可执行文件，默认会自动从全局PATH中寻找php
- -c, --start-cmd 指定的server启动命令，默认是http:start（启动http server）
- -w, --watch 指定要监控的目录，相对于应用目录，默认监控app及config目录下的文件变动
## 使用示例
php swoftcli.phar run -c http:start -b bin/swoft
运行成功：
    $ php swoftcli.phar run -c http:start -b bin/swoft
    Work Information
      current pid 16513
      current dir /Users/gaobinzhan/Desktop/swoft
      php binFile /usr/local/bin/php
      target path /Users/gaobinzhan/Desktop/swoft
      watch dirs  app, config
      entry file  /Users/gaobinzhan/Desktop/swoft/bin/swoft
      execute cmd /usr/local/bin/php /Users/gaobinzhan/Desktop/swoft/bin/swoft http:start
    
    Watched Directories
      /Users/gaobinzhan/Desktop/swoft/app
      /Users/gaobinzhan/Desktop/swoft/config
    
    2020/04/21-02:53:08 [SWOFTCLI] Start swoft server
当有文件发生变动时，swoft-cli 就会自动重启应用

> ⚠️使用 swoftcli 监控 server 开发时，不能将 server 配置为后台运行，不然 swoftcli 会错误的认为 server 意外退出了，导致重复启动。

# 生成类应用文件
自 swoftcli1.0 开始，就支持通过内置命令快速创建一个应用类。

支持创建：
- cli-command
- crontab
- http-controller
- http-middleware
- listener
- process
- rpc-controller
- rpc-middleware
- task
- tcp-controller
- tcp-middleware
- ws-controller
- ws-middleware
- ws-module

## 查看命令组
    php swoftcli.phar gen
    // or
    php swoftcli.phar gen --help
运行结果：
    
    $ php swoftcli.phar gen
    
    Generate some common application template classes
    
    Group: gen (alias: generate)
    Usage:
      swoftcli.phar gen:COMMAND [--opt ...] [arg ...]
    
    Global Options:
          --debug      Setting the application runtime debug level(0 - 4)
          --no-color   Disable color/ANSI for message output
      -h, --help       Display help message for application or command
      -V, --version    Display application version information
    
    Commands:
      cli-command     Generate CLI command controller class(alias: cmd, command)
      crontab         Generate user cronTab task class(alias: task-crontab, taskCrontab)
      http-controller Generate HTTP controller class(alias: ctrl, http-ctrl)
      http-middleware Generate HTTP middleware class(alias: http-mdl, httpmdl, http-middle)
      listener        Generate an event listener class(alias: event-listener)
      process         Generate user custom process class(alias: proc)
      rpc-controller  Generate RPC service class(alias: rpcctrl, service, rpc-ctrl)
      rpc-middleware  Generate RPC middleware class(alias: rpcmdl, rpc-mdl, rpc-middle)
      task            Generate user task class
      tcp-controller  Generate TCP controller class(alias: tcpc, tcpctrl, tcp-ctrl)
      tcp-middleware  Generate TCP middleware class(alias: tcpmdl, tcp-mdl, tcp-middle)
      ws-controller   Generate WebSocket message controller class(alias: wsc, wsctrl, ws-ctrl)
      ws-middleware   Generate WebSocket middleware class(alias: wsmdl, ws-mdl, ws-middle)
      ws-module       Generate WebSocket module class(alias: wsm, wsmod, ws-mod, wsModule)
    
    View the specified command, please use: swoftcli.phar gen:COMMAND -h
## 命令使用
- 生成 http controller
`php swoftcli.phar gen:http-ctrl demo --prefix /demo`
- 生成 http middleware
`php swoftcli.phar gen:http-mdl demo`
- 生成 websocket middleware
`php swoftcli.phar gen:ws-mod demo --prefix /demo`
- 生成 websocket controller
`php swoftcli.phar gen:ws-ctrl demo`

# 创建新应用或组件
自 swoftcli1.0 开始，支持通过内置命令快速的创建一个新的应用骨架，或者创建一个新的组件骨架结构。
## 查看命令组
    php swoftcli.phar new
    // or use alias
    php swoftcli.phar create
运行结果：
    
    $ php swoftcli.phar new
    
    Provide some commads for quick create new application or component
    
    Group: new (alias: create)
    Usage:
      swoftcli.phar new:COMMAND [--opt ...] [arg ...]
    
    Global Options:
          --debug      Setting the application runtime debug level(0 - 4)
          --no-color   Disable color/ANSI for message output
      -h, --help       Display help message for application or command
      -V, --version    Display application version information
    
    Commands:
      application     Quick crate an new swoft application project(alias: a, app, project)
      component       Quick crate an new swoft component project(alias: c, cpt)
    
    View the specified command, please use: swoftcli.phar new:COMMAND -h

命令组说明：
- 命令组new（别名create）,任意一个都可以
- 拥有两个子命令 application和component 分别用于创建新应用和新的组件结构

## 创建新应用
创建新应用是通过拉取github上已存在的模板项目仓库，因此您可以轻松自定义符合自己需求的模板。
swoft默认提供了5个模板仓库，方便用户根据需要拉取不同的骨架结构。可以在下面的命令帮助中看到有哪几个默认骨架。
    php swoftcli.phar create:app -h
    // or use alias
    php swoftcli.phar create:app
运行结果：
    
    $ php swoftcli.phar create:app -h
    
    Quick crate an new swoft application project
    
    Usage:
      swoftcli.phar create:app [arguments ...] [options ...]
    
    Global Options:
          --debug      Setting the application runtime debug level(0 - 4)
          --no-color   Disable color/ANSI for message output
      -h, --help       Display help message for application or command
      -V, --version    Display application version information
    
    Arguments:
      name STRING   The new application project name
    
    Options:
          --no-install     Dont run composer install after new application created
          --refresh     Whether remove old tmp caches before create new application
          --repo STRING    Custom the template repository url for create new application
          --type STRING    The crate new application project type. allow: http, ws, tcp, rpc, full (defaults: http)
      -y, --yes         Whether need to confirm operation (defaults: False)
    
    Example:
      swoftcli.phar create:app --type ws
      swoftcli.phar create:app --type tcp
      swoftcli.phar create:app --repo https://github.com/UESRNAME/my-swoft-skeleton.git
    
    Default template repos:
    
    TYPE   Github Repository URL
    -----|------------------------------------------------
    http   https://github.com/swoft-cloud/swoft-http-project.git
    tcp    https://github.com/swoft-cloud/swoft-tcp-project.git
    rpc    https://github.com/swoft-cloud/swoft-rpc-project.git
    ws     https://github.com/swoft-cloud/swoft-ws-project.git
    full   https://github.com/swoft-cloud/swoft.git
### 命令使用说明
命令参数：

name 设置新项目名称
命令选项：

- --repo 自定义设置模板仓库的git地址，可以是 UERANME/REPO 或者 完整url地址
- --type 从默认的提供的5个模板仓库里选择一个来作为源仓库，默认是 http
> YOUR_APP_NAME 指的是你的新项目名称，同时也是作为新应用的目录名。

直接使用 create:app YOUR_APP_NAME，默认使用 swoft-http-project 模板仓库。 如果需要ws/tcp/rpc等模板仓库作为基础模板，可以如下指定 type 选项。

    php swoftcli.phar create:app --type ws YOUR_APP_NAME
    php swoftcli.phar create:app --type tcp YOUR_APP_NAME
如果你需要对模板做一些完全的自定义，那你就可以在自己的github创建需要的模板仓库，然后使用如下命令来使用：

    php swoftcli.phar create:app --repo UERANME/REPO YOUR_APP_NAME
使用的完整的git仓库地址；这时不限于从github拉取，即也可以从自己的git服务拉取来初始化一个新应用。

    php swoftcli.phar create:app --repo https://github.com/UERANME/REPO.git YOUR_APP_NAME

### 使用命令创建新应用
我们快来试试创建新应用的命令，我好激动啊！✌️

    php swoftcli.phar create:app --type http my-swoft-app
运行结果：
    
    $ php swoftcli.phar create:app --type http my-swoft-app
    
    - Validate project information
    Project Information
      name        my-swoft-app
      type        http
      repoUrl     https://github.com/swoft-cloud/swoft-http-project.git
      workDir     /Users/gaobinzhan/Desktop/swoft
      projectPath /Users/gaobinzhan/Desktop/swoft/my-swoft-app
    
    Ensure create application ?
    Please confirm(yes|no)[default:yes]: - Begin create the new project: my-swoft-app
    - Copy project files from local cache
    - Remove .git directory on new project
    - Project: /Users/gaobinzhan/Desktop/swoft/my-swoft-app created
    - Begin run composer install for init project
    
    
    Loading composer repositories with package information
## 创建新组件
应用既然都创建啦，我们接着来创建组件

> 骨架结构非常简单，包含 composer.json` README.md AutoLoader.php` 等基础文件

查看命令

    php swoftcli.phar create:component --help
    // or use alias
    php swoftcli.phar create:c
运行结果
    
    $ php swoftcli.phar create:component --help
    
    Quick crate an new swoft component project
    
    Usage:
      swoftcli.phar create:component [arguments ...] [options ...]
    
    Global Options:
          --debug      Setting the application runtime debug level(0 - 4)
          --no-color   Disable color/ANSI for message output
      -h, --help       Display help message for application or command
      -V, --version    Display application version information
    
    Arguments:
      name STRING   The new component project name
    
    Options:
      -n, --namespace STRING     Namespace of the new component
          --no-license           Dont add the apache license file (defaults: False)
      -o, --output STRING        The output dir for new component, default is crate at current dir
          --pkg-name STRING      The new component package name, will write to composer.json
      -y, --yes                  Whether need to confirm operation (defaults: False)
    
    Example:
      swoftcli.phar create:component demo -n 'My\Component'
      swoftcli.phar create:component demo -n 'My\Component' -o vendor/somedir