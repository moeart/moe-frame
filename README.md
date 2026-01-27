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

[中文版本 (Chinese Version)](README_zh.md)

A lightweight, open-source PHP framework originally developed for microservices at acgdraw.com, now designed for building various microservices efficiently.

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [MoeApps API](#moeapps-api)
- [Helper Functions](#helper-functions)
- [Router Configuration](#router-configuration)
- [Crontab Configuration](#crontab-configuration)
- [Maidchan Tool](#maidchan-tool)
- [Nginx Configuration](#nginx-configuration)
- [Developers](#developers)

## Introduction

MoeFrame is a lightweight, high-performance, open-source PHP framework. Initially developed for building microservices at acgdraw.com, it has evolved into a versatile solution suitable for developing various microservices. The framework focuses on simplicity, performance, and developer experience, providing a solid foundation for building web applications and microservices with PHP.

## Features

- **Lightweight**: Minimal core with essential functionality
- **MVC Architecture**: Clear separation of concerns
- **Routing System**: Flexible URL routing
- **Template Engine**: Simple and powerful template system
- **Error Handling**: Comprehensive error and exception handling
- **Security**: Built-in security features
- **Extensible**: Easy to extend with custom components

## Requirements

- PHP 8.0 or higher

## Installation

1. Clone repository:

```bash
git clone https://github.com/moeart/moe-frame.git
cd moe-frame
```

2. Configure environment variables (optional):

Create or edit `env.json` file at project root:

```json
{
    "API_KEY": "your-api-key-here",
    "UPLOAD_PATH": "E('MOEFRAME_STORAGE')/uploads"
}
```

3. Install dependencies (optional):

MoeFrame is compatible with Composer for dependency management, but currently does not provide official Composer packages. If you want to use Composer for your own dependencies:

```bash
composer install
```

## Usage

### Basic Application Example

MoeFrame applications are defined in the `app/` directory. Here's an example application:

**File: `app/helloWorld.php`**

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

MoeApps is the base class for all applications in MoeFrame. It provides various methods for handling HTTP responses, rendering views, and managing data.

### Direct Show

Directly display content and exit:

```php
public function directshow($content)
```

**Example:**

```php
public function showContent() {
    $this->directshow('Hello, World!');
}
```

### JSON Response

Return JSON response:

```php
public function json($content)
```

**Example:**

```php
public function apiData() {
    $data = ['status' => 'success', 'message' => 'Data retrieved'];
    $this->json($data);
}
```

### HTTP Header

Return HTTP status code with optional body:

```php
public function header($code, $body = '')
```

**Example:**

```php
public function customResponse() {
    $this->header(200, 'Custom response body');
}
```

### Abort

Abort loading and return HTTP status code with optional custom view:

```php
public function abort($code, $view = '', $msg = '')
```

**Example:**

```php
public function checkAccess() {
    if (!isset($_SESSION['user'])) {
        $this->abort(403, '', 'Access denied: User not logged in');
    }
}
```

### View Render

Render a view with parameters:

```php
public static function viewrender($view, $parameters = array())
```

**Example:**

```php
public function showPage() {
    $this->viewrender('welcome', array(
        'title' => 'Welcome to MoeFrame',
        'content' => 'This is a sample page'
    ));
}
```

### JSON Body

Get POST body of JSON type:

```php
public function jsonbody()
```

**Example:**

```php
public function receiveData() {
    $data = $this->jsonbody();
    // Process JSON data
}
```

### Cookie

Get COOKIES from HTTP request:

```php
public function cookie()
```

**Example:**

```php
public function checkCookie() {
    $cookies = $this->cookie();
    if (isset($cookies['session'])) {
        // Process session cookie
    }
}
```

### Empty Response

Show empty response with HTTP status code:

```php
public function empty($code = 200)
```

**Example:**

```php
public function noContent() {
    $this->empty(204);
}
```

## Helper Functions

MoeFrame provides helper functions for common tasks like environment configuration and SDK imports.

### Environment Function

Get environment variables or predefined constants:

```php
function E($envname)
```

**Predefined Constants:**
- `MOEFRAME_ROOT`: Root directory of the framework
- `MOEFRAME_VENDOR`: Vendor directory for third-party libraries
- `MOEFRAME_STORAGE`: Storage directory for application data
- `MOEFRAME_TMP_ROOT`: Temporary directory for temporary files

**Custom Environment Variables:**
Custom environment variables can be defined in the `env.json` file at the project root.

**Example:**

```php
// Get predefined constants
$root = E('MOEFRAME_ROOT');
$vendor = E('MOEFRAME_VENDOR');
$storage = E('MOEFRAME_STORAGE');
$tmp = E('MOEFRAME_TMP_ROOT');

// Get custom environment variables from env.json
$apiKey = E('API_KEY');

// Use placeholders in env.json values
// If env.json contains: "UPLOAD_PATH": "E('MOEFRAME_STORAGE')/uploads"
$uploadPath = E('UPLOAD_PATH'); // Returns the resolved path
```

**env.json Example:**

```json
{
    "API_KEY": "your-api-key-here",
    "UPLOAD_PATH": "E('MOEFRAME_STORAGE')/uploads"
}
```

### Import SDK Function

Import third-party SDKs from the vendor directory:

```php
function ImportSdk($sdkName)
```

This function automatically loads SDKs by checking for common autoload files.

**Parameters:**
- `$sdkName`: SDK path in format `${VendorName}/${SdkName}`

**Example:**

```php
// Import SDK with autoload.php
ImportSdk('moeart/demoSdk');

// Import SDK with bootstrap.php
ImportSdk('vendor/package');
```

The function will look for:
1. `vendor/${VendorName}/${SdkName}/autoload.php`
2. `vendor/${VendorName}/${SdkName}/bootstrap.php`

## Router Configuration

Routing is configured in the `conf/route.inc.php` file. The MoeRouter provides a flexible routing system that supports various routing patterns and middleware.

**File: `conf/route.inc.php`**

### Basic Routing

Define simple routes using the `R()` method:

```php
// Basic route to a controller method
$MoeRouter->R('/', 'ExampleApp@Hello');
$MoeRouter->R('/hello', 'ExampleApp@Hello');
```

### Hostname Group Routing

Group routes by hostname using the `H()` method:

```php
$MoeRouter->H([
    "hostname-a.example.com",
    "hostname-b.example.net:8038",
    "192.168.0.1:8081"
], function($MoeRouter) 
{
    // Routes in this group will only match the specified hostnames
    $MoeRouter->R('/', 'ExampleApp@Hello');
    $MoeRouter->R('/hostname/group', 'ExampleApp@Hello');
});
```

### Regular Expression Routing

Use regex patterns for flexible URL matching:

```php
// Match URLs like /regex/a, /regex/c, /regex/g, /regex/d, etc.
$MoeRouter->R('/regex/?[acg,draw]', 'ExampleApp@Hello');
```

### Middleware

Apply middleware filters to routes for access control and validation:

```php
// Middleware with CIDR whitelist and hostname whitelist
$MoeRouter->R('/middleware/cidr', 'ExampleApp@Hello', [
    "cidr_whitelist" => [
        "192.168.1.0/24",  // Allow access from this IP range
        "10.0.0.0/8"          // Allow access from this IP range
    ],
    "hosts_whitelist" => [
        "www.example.com"  // Allow access from this hostname
    ]
]);

// Middleware with hostname group
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

### Middleware Types

MoeFrame supports several middleware types:

- **cidr_whitelist**: Restrict access based on IP address ranges (CIDR notation)
- **hosts_whitelist**: Restrict access based on hostname

### Complete Example

Here's a complete routing configuration example:

```php
<?php

// Hostname group routing
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

// Regular expression routing
$MoeRouter->R('/regex/?[acg,draw]', 'ExampleApp@Hello');

// Middleware routing
$MoeRouter->R('/middleware/cidr', 'ExampleApp@Hello', [
    "cidr_whitelist" => [
        "192.168.1.0/24",
        "10.0.0.0/8"
    ],
    "hosts_whitelist" => [
        "www.example.com"
    ]
]);

// Regular routing
$MoeRouter->R('/', 'ExampleApp@Hello');
$MoeRouter->R('/hello', 'ExampleApp@Hello');

?>
```

## Crontab Configuration

Scheduled tasks are configured in the `conf/crontab.inc.php` file. The MoeCrontab class provides a simple way to define and manage cron jobs.

**File: `conf/crontab.inc.php`**

### Basic Setup

First, create a MoeCrontab instance:

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

### Defining Scheduled Tasks

Use the `C()` method to define scheduled tasks:

```php
// Add a crontab job: every 5 minutes, run ExampleApp@Hello
$MoeCrontab->C('*/5 * * * *', 'ExampleApp@Hello');

// Add a crontab job: run every hour
$MoeCrontab->C('0 * * * *', 'ExampleApp@HourlyTask');

// Add a crontab job: run every day at midnight
$MoeCrontab->C('0 0 * * *', 'ExampleApp@DailyTask');

// Add a crontab job: run every Monday at 9:00 AM
$MoeCrontab->C('0 9 * * 1', 'ExampleApp@WeeklyTask');
```

### Crontab Expression Format

Crontab expressions follow the standard format: `* * * * *`

```
┌───────────── minute (0 - 59)
│ ┌───────────── hour (0 - 23)
│ │ ┌───────────── day of month (1 - 31)
│ │ │ ┌───────────── month (1 - 12)
│ │ │ │ ┌───────────── day of week (0 - 7) (Sunday to Saturday)
│ │ │ │ │
* * * * *
```

**Common Examples:**

| Expression | Description |
|-----------|-------------|
| `* * * * *` | Every minute |
| `*/5 * * * *` | Every 5 minutes |
| `0 * * * *` | Every hour |
| `0 0 * * *` | Every day at midnight |
| `0 9 * * 1` | Every Monday at 9:00 AM |
| `0 9-17 * * 1-5` | Every weekday (Mon-Fri) from 9:00 AM to 5:00 PM |
| `0 0,12 * * *` | Twice a day at midnight and noon |
| `0 0 1 * *` | On the 1st of every month at midnight |

### Running Scheduled Tasks

Execute scheduled tasks using the maidchan tool:

```bash
php maidchan crontab:run
```

This command will:
1. Load the crontab configuration from `conf/crontab.inc.php`
2. Check which tasks should run at the current time
3. Execute the matching tasks
4. Display the execution status

### Complete Example

Here's a complete crontab configuration example:

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

## Maidchan Tool

Maidchan is a command-line tool for MoeFrame that helps with development server and scheduled tasks. It's located at `maidchan`.

### Usage

#### Start Development Server

```bash
php maidchan run               - Start development server at default address 0.0.0.0:8000
php maidchan run -l 127.0.0.1  - Start development server at specified address 127.0.0.1:8000
php maidchan run -p 8888       - Start development server at default address 0.0.0.0:8888
php maidchan run -l 127.0.0.1 -p 8888 - Start development server at specified address 127.0.0.1:8888
```

#### Run Scheduled Tasks

```bash
php maidchan crontab:run    - Run scheduled tasks
```

## Nginx Configuration

Here's an example Nginx configuration for MoeFrame:

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

    # Deny access to sensitive files
    location ~ /\.(env|git|svn) {
        deny all;
    }

    # Set proper headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options SAMEORIGIN;
    add_header X-XSS-Protection "1; mode=block";
}
```

## Developers

- **Developer Team**: 萌艺科技开发组
- **Company**: 长沙萌艺科技有限责任公司
- **Website**: [www.acgdraw.com](https://www.acgdraw.com)
- **GitHub**: [github.com/moeart](https://github.com/moeart)
