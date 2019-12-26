Info
-------
jwt 刷新token 黑名单

Request
-------
```
"lcobucci/jwt": "^3.3"
```

Usage
-----
```php


<?php
    // 方法一
    $config = [
        //加密类型, 支持类型请看 supportedAlgs
        'alg'        => 256,
        //私钥，可以是字符串也可以是文件路径
        'privateKey' => '',
        //公钥，可以是字符串也可以是文件路径
        'publicKey'  => '',
        //key,
        'key'        => 'ice',
        //发行人
        'iss'        => '',
        //接受者
        'aud'        => '',
        //在多少秒之前不可使用
        'nbf'        => 0,
        //过期时间
        'exp'        => 7200,
        // 黑名单缓存类,
        'cache'      => \Cache::class, // implement Psr\SimpleCache\CacheInterface
    ];
    $jwt = new Jwt($config);
    // make
    $jwt->makeToken(["a" => '1', "b" => '2'], 1);
    $exp = 604800;
    $refreshToken = $jwt->makeRefreshToken($exp);
    
    // validate
    $token = '';
    $jwt->verify($token);
     // get params
    $jwt->claimsAsArray();

     // refreshToken
    $jwt->refreshToken($refreshToken);

    $jwt->logout();

    // 方法二  依赖注入
    //自己去实现
    // OauthInterface => TokenFactory
    // TokenFactory 实现方法一
```



