Info
-------
jwt 带 刷新token
token 黑名单

Request
-------
```
    "easyswoole/jwt": "^1.1"
```

Install
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require lengbin/jwt
```

or add

```
"lengbin/jwt": "*"
```
to the require section of your `composer.json` file.

Usage
-----
```php

<?php
    use Lengbin\Jwt\Config;
    use Lengbin\Jwt\Jwt;
    /**
     *
     * @var $cache Psr\SimpleCache\CacheInterface
     */
    $cache = new Cache();
    
    // 具体配置 情况 config 文件
    // 支持 单点登录
    $config = new Config();
    $jwt = new Jwt($cache, $config);
    
    // 生成token
    $token = $jwt->generate(['id'=>1, 'test'=>1]);
    
    // 生成刷新token
    $refreshToken = $jwt->generateRefreshToken($token);

    // 验证 token 获得 数据
    $data = $jwt->verifyToken($token);
  
    // 通过刷新token 更新 token
    $refreshToken2 = $jwt->refreshToken($refreshToken);
   
    // 注销
    $jwt->logout($token);
```



