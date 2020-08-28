<?php

namespace Lengbin\Jwt;

use Lengbin\Helper\Component\BaseObject;

class Config extends BaseObject
{
    /**
     * 加密方式
     * @var string
     */
    public $alg = "HMACSHA256";

    /**
     * 秘钥key
     * @var string
     */
    public $key = "jwt";

    /**
     * jwt 过期时间
     * @var float|int
     */
    public $exp = 2 * 3600;

    /**
     * 刷新token 时间
     * @var float|int
     */
    public $ttl = 24 * 3600 * 30;

    /**
     * 单点登录
     * @var bool
     */
    public $sso = false;

    /**
     * 如果开启 单点登录，请设置 key
     *
     * @var string
     */
    public $ssoKey = 'id';

}
