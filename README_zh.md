# MoeFrame
```
888b     d888                   8888888888                                     
8888b   d8888                   888                                            
88888b.d88888                   888                                            
888Y88888P888  .d88b.   .d88b.  8888888 888d888 8888b.  88888b.d88b.   .d88b.  
888 Y888P 888 d88""88b d8P  Y8b 888     888P"      "88b 888 "888 "88b d8P  Y8b 
888  Y8P  888 888  888 88888888 888     888    .d888888 888  888  888 88888888 
888   "   888 Y88..88P Y8b.     888     888    888  888 888  888  888 Y8b.     
888       888  "Y88P"   "Y8888  888     888    "Y888888 888  888  888  "Y8888  
```
![PHP](https://img.shields.io/badge/PHP-^8.0-blue.svg) ![License](https://img.shields.io/badge/License-MIT-green.svg) ![Version](https://img.shields.io/badge/Version-2.0.2026.0127-orange.svg)

[English Version](README.md)

一个轻量级、高性能的开源PHP框架，最初为萌绘图站（acgdraw.com）的微服务开发而创建，现专为高效构建各种微服务而设计。

## 目录

- [简介](#简介)
- [特性](#特性)
- [要求](#要求)
- [安装](#安装)
- [使用](#使用)
- [MoeApps API](#moeapps-api)
- [辅助函数](#辅助函数)
- [路由配置](#路由配置)
- [定时任务配置](#定时任务配置)
- [Maidchan工具](#maidchan工具)
- [Nginx配置](#nginx配置)
- [开发者](#开发者)

## 简介

MoeFrame是一个轻量级、高性能的开源PHP框架。最初为萌绘图站（acgdraw.com）的微服务开发而创建，现已发展成为一个通用解决方案，适合开发各种微服务。框架专注于简洁性、性能和开发者体验，为使用PHP构建Web应用程序和微服务提供了坚实的基础。

## 特性

- **轻量级**：最小核心，包含基本功能
- **MVC架构**：清晰的关注点分离
- **路由系统**：灵活的URL路由
- **模板引擎**：简单而强大的模板系统
- **错误处理**：全面的错误和异常处理
- **安全性**：内置安全特性
- **可扩展**：易于使用自定义组件扩展

## 要求

- PHP 8.0或更高版本

## 安装

1. 克隆仓库：

```bash
git clone https://github.com/moeart/moe-frame.git
cd moe-frame
```

2. 配置环境变量（可选）：

在项目根目录创建或编辑 `env.json` 文件：

```json
{
    "API_KEY": "your-api-key-here",
    "UPLOAD_PATH": "E('MOEFRAME_STORAGE')/uploads"
}
```

3. 安装依赖（可选）：

MoeFrame兼容Composer进行依赖管理，但目前暂不提供官方Composer包。如果您想使用Composer管理自己的依赖：

```bash
composer install
```

## 使用

### 基本应用示例

MoeFrame应用程序定义在 `app/` 目录中。以下是一个示例应用程序：

**文件：`app/helloWorld.php`**

```php
<?php
class ExampleApp extends MoeApps {

    public function Hello() {

        if( isset($_GET['test']) )
            $content = "You Clicked On Test!";
        else
            $content = "Nice to meet you!";
                    
        $this->viewrender('welcome', array(
              'title' => 'Welcome to MoeFrame',
            'content' => $content,
            'version' => '1.0'
        ));
            
    }

}
?>
```

## MoeApps API

MoeApps是MoeFrame中所有应用程序的基类。它提供了各种方法来处理HTTP响应、渲染视图和管理数据。

### 直接显示

直接显示内容并退出：

```php
public function directshow($content)
```

**示例：**

```php
public function showContent() {
    $this->directshow('Hello, World!');
}
```

### JSON响应

返回JSON响应：

```php
public function json($content)
```

**示例：**

```php
public function apiData() {
    $data = ['status' => 'success', 'message' => 'Data retrieved'];
    $this->json($data);
}
```

### HTTP头部

返回HTTP状态码和可选的响应体：

```php
public function header($code, $body = '')
```

**示例：**

```php
public function customResponse() {
    $this->header(200, 'Custom response body');
}
```

### 中止

中止加载并返回HTTP状态码，可选择自定义视图：

```php
public function abort($code, $view = '', $msg = '')
```

**示例：**

```php
public function checkAccess() {
    if (!isset($_SESSION['user'])) {
        $this->abort(403, '', 'Access denied: User not logged in');
    }
}
```

### 视图渲染

渲染视图并传递参数：

```php
public static function viewrender($view, $parameters = array())
```

**示例：**

```php
public function showPage() {
    $this->viewrender('welcome', array(
        'title' => 'Welcome to MoeFrame',
        'content' => 'This is a sample page'
    ));
}
```

### JSON请求体

获取JSON类型的POST请求体：

```php
public function jsonbody()
```

**示例：**

```php
public function receiveData() {
    $data = $this->jsonbody();
    // 处理JSON数据
}
```

### Cookie

从HTTP请求中获取COOKIES：

```php
public function cookie()
```

**示例：**

```php
public function checkCookie() {
    $cookies = $this->cookie();
    if (isset($cookies['session'])) {
        // 处理会话cookie
    }
}
```

### 空响应

显示空响应和HTTP状态码：

```php
public function empty($code = 200)
```

**示例：**

```php
public function noContent() {
    $this->empty(204);
}
```

## 辅助函数

MoeFrame提供了辅助函数用于常见任务，如环境配置和SDK导入。

### 环境函数

获取环境变量或预定义常量：

```php
function E($envname)
```

**预定义常量：**
- `MOEFRAME_ROOT`：框架根目录
- `MOEFRAME_VENDOR`：第三方库的vendor目录
- `MOEFRAME_STORAGE`：应用数据的存储目录
- `MOEFRAME_TMP_ROOT`：临时文件的临时目录

**自定义环境变量：**
自定义环境变量可以在项目根目录的 `env.json` 文件中定义。

**示例：**

```php
// 获取预定义常量
$root = E('MOEFRAME_ROOT');
$vendor = E('MOEFRAME_VENDOR');
$storage = E('MOEFRAME_STORAGE');
$tmp = E('MOEFRAME_TMP_ROOT');

// 从env.json获取自定义环境变量
$apiKey = E('API_KEY');

// 在env.json值中使用占位符
// 如果env.json包含: "UPLOAD_PATH": "E('MOEFRAME_STORAGE')/uploads"
$uploadPath = E('UPLOAD_PATH'); // 返回解析后的路径
```

**env.json 示例：**

```json
{
    "API_KEY": "your-api-key-here",
    "UPLOAD_PATH": "E('MOEFRAME_STORAGE')/uploads"
}
```

### 导入SDK函数

从vendor目录导入第三方SDK：

```php
function ImportSdk($sdkName)
```

此函数通过检查常见的自动加载文件来自动加载SDK。

**参数：**
- `$sdkName`：SDK路径，格式为 `${VendorName}/${SdkName}`

**示例：**

```php
// 导入带有autoload.php的SDK
ImportSdk('moeart/demoSdk');

// 导入带有bootstrap.php的SDK
ImportSdk('vendor/package');
```

该函数将查找：
1. `vendor/${VendorName}/${SdkName}/autoload.php`
2. `vendor/${VendorName}/${SdkName}/bootstrap.php`

## 路由配置

路由配置在 `conf/route.inc.php` 文件中。MoeRouter提供了灵活的路由系统，支持各种路由模式和中间件。

**文件：`conf/route.inc.php`**

### 基本路由

使用 `R()` 方法定义简单路由：

```php
// 基本路由到控制器方法
$MoeRouter->R('/', 'ExampleApp@Hello');
$MoeRouter->R('/hello', 'ExampleApp@Hello');
```

### 主机名分组路由

使用 `H()` 方法按主机名分组路由：

```php
$MoeRouter->H([
    "hostname-a.example.com",
    "hostname-b.example.net:8038",
    "192.168.0.1:8081"
], function($MoeRouter) 
{
    // 此组中的路由仅匹配指定的主机名
    $MoeRouter->R('/', 'ExampleApp@Hello');
    $MoeRouter->R('/hostname/group', 'ExampleApp@Hello');
});
```

### 正则表达式路由

使用正则表达式模式进行灵活的URL匹配：

```php
// 匹配像 /regex/a, /regex/c, /regex/g, /regex/d 等URL
$MoeRouter->R('/regex/?[acg,draw]', 'ExampleApp@Hello');
```

### 中间件

对路由应用中间件过滤器，用于访问控制和验证：

```php
// 带有CIDR白名单和主机名白名单的中间件
$MoeRouter->R('/middleware/cidr', 'ExampleApp@Hello', [
    "cidr_whitelist" => [
        "192.168.1.0/24",  // 允许从此IP范围访问
        "10.0.0.0/8"          // 允许从此IP范围访问
    ],
    "hosts_whitelist" => [
        "www.example.com"  // 允许从此主机名访问
    ]
]);

// 带有主机名分组的中间件
$MoeRouter->H([
    "hostname-a.example.com",
    "hostname-b.example.net:8038"
], function($MoeRouter) 
{
    $middleware = [
        "cidr_whitelist" => [
            "192.168.1.0/24",
            "10.0.0.0/8"
        ]
    ];
    $MoeRouter->R('/', 'ExampleApp@Hello');
    $MoeRouter->R('/hostname/group', 'ExampleApp@Hello', $middleware);
});
```

### 中间件类型

MoeFrame支持几种中间件类型：

- **cidr_whitelist**：基于IP地址范围（CIDR表示法）限制访问
- **hosts_whitelist**：基于主机名限制访问

### 完整示例

这是一个完整的路由配置示例：

```php
<?php

// 主机名分组路由
$MoeRouter->H([
    "hostname-a.example.com",
    "hostname-b.example.net:8038",
    "192.168.0.1:8081"
], function($MoeRouter) 
{
    $middleware = [
        "cidr_whitelist" => [
            "192.168.1.0/24",
            "10.0.0.0/8"
        ]
    ];
    $MoeRouter->R('/', 'ExampleApp@Hello');
    $MoeRouter->R('/hostname/group', 'ExampleApp@Hello', $middleware);
});

// 正则表达式路由
$MoeRouter->R('/regex/?[acg,draw]', 'ExampleApp@Hello');

// 中间件路由
$MoeRouter->R('/middleware/cidr', 'ExampleApp@Hello', [
    "cidr_whitelist" => [
        "192.168.1.0/24",
        "10.0.0.0/8"
    ],
    "hosts_whitelist" => [
        "www.example.com"
    ]
]);

// 常规路由
$MoeRouter->R('/', 'ExampleApp@Hello');
$MoeRouter->R('/hello', 'ExampleApp@Hello');

?>
```

## 定时任务配置

定时任务配置在 `conf/crontab.inc.php` 文件中。MoeCrontab类提供了一种简单的方法来定义和管理cron作业。

**文件：`conf/crontab.inc.php`**

### 基本设置

首先，创建一个MoeCrontab实例：

```php
<?php
/**
 * Crontab Configuration
 */

// Create MoeCrontab instance
global $MoeCrontab;
$MoeCrontab = new MoeCrontab();
?>
```

### 定义定时任务

使用 `C()` 方法定义定时任务：

```php
// 添加一个cron作业：每5分钟运行一次ExampleApp@Hello
$MoeCrontab->C('*/5 * * * *', 'ExampleApp@Hello');

// 添加一个cron作业：每小时运行一次
$MoeCrontab->C('0 * * * *', 'ExampleApp@HourlyTask');

// 添加一个cron作业：每天午夜运行一次
$MoeCrontab->C('0 0 * * *', 'ExampleApp@DailyTask');

// 添加一个cron作业：每周一上午9:00运行一次
$MoeCrontab->C('0 9 * * 1', 'ExampleApp@WeeklyTask');
```

### Crontab表达式格式

Crontab表达式遵循标准格式：`* * * * *`

```
┌───────────── 分钟 (0 - 59)
│ ┌───────────── 小时 (0 - 23)
│ │ ┌───────────── 月份中的日期 (1 - 31)
│ │ │ ┌───────────── 月份 (1 - 12)
│ │ │ │ ┌───────────── 星期几 (0 - 7) (星期日到星期六)
│ │ │ │ │
* * * * *
```

**常见示例：**

| 表达式 | 描述 |
|-----------|-------------|
| `* * * * *` | 每分钟 |
| `*/5 * * * *` | 每5分钟 |
| `0 * * * *` | 每小时 |
| `0 0 * * *` | 每天午夜 |
| `0 9 * * 1` | 每周一上午9:00 |
| `0 9-17 * * 1-5` | 每工作日（周一至周五）上午9:00至下午5:00 |
| `0 0,12 * * *` | 每天午夜和中午各一次 |
| `0 0 1 * *` | 每月1日午夜 |

### 运行定时任务

使用maidchan工具执行定时任务：

```bash
php maidchan crontab:run
```

此命令将：
1. 从 `conf/crontab.inc.php` 加载crontab配置
2. 检查当前时间应该运行哪些任务
3. 执行匹配的任务
4. 显示执行状态

### 完整示例

这是一个完整的crontab配置示例：

```php
<?php
/**
 * Crontab Configuration
 */

// Create MoeCrontab instance
global $MoeCrontab;
$MoeCrontab = new MoeCrontab();

// Add a crontab job: every 5 minutes, run ExampleApp@Hello
$MoeCrontab->C('*/5 * * * *', 'ExampleApp@Hello');

// Add a crontab job: run every hour
$MoeCrontab->C('0 * * * *', 'ExampleApp@HourlyTask');

// Add a crontab job: run every day at midnight
$MoeCrontab->C('0 0 * * *', 'ExampleApp@DailyTask');

// Add a crontab job: run every Monday at 9:00 AM
$MoeCrontab->C('0 9 * * 1', 'ExampleApp@WeeklyTask');

?>
```

## Maidchan工具

Maidchan是MoeFrame的命令行工具，用于开发服务器和定时任务。它位于 `maidchan`。

### 使用方法

#### 启动开发服务器

```bash
php maidchan run               - 在默认地址 0.0.0.0:8000 启动开发服务器
php maidchan run -l 127.0.0.1  - 在指定地址 127.0.0.1:8000 启动开发服务器
php maidchan run -p 8888       - 在默认地址 0.0.0.0:8888 启动开发服务器
php maidchan run -l 127.0.0.1 -p 8888 - 在指定地址 127.0.0.1:8888 启动开发服务器
```

#### 运行定时任务

```bash
php maidchan crontab:run    - 运行定时任务
```

## Nginx配置

以下是MoeFrame的Nginx配置示例：

```nginx
server {
    listen 80;
    server_name example.com www.example.com;
    root /path/to/moe-frame/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 禁止访问敏感文件
    location ~ /\.(env|git|svn) {
        deny all;
    }

    # 设置适当的头部
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options SAMEORIGIN;
    add_header X-XSS-Protection "1; mode=block";
}
```

## 开发者

- **开发团队**：萌艺科技开发组
- **公司**：长沙萌艺科技有限责任公司
- **网站**：[萌绘图站](https://www.acgdraw.com)
- **GitHub**：[github.com/moeart](https://github.com/moeart)
