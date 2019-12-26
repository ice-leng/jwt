<?php
declare(strict_types=1);

namespace Lengbin\Jwt;

interface TokenInterface
{

    /**
     * 生成token
     *
     * @param array           $params
     * @param int|string|null $jti
     *
     * @return string
     */
    public function makeToken(array $params = [], $jti = null);

    /**
     * 验证
     *
     * @param string $token
     *
     * @return bool
     */
    public function verify(string $token);

    /**
     * 获得 自定义参数
     * @return array
     */
    public function getParams();

    /**
     * 生成 刷新token
     *
     * @param int $exp 过期时间 默认 7天
     *
     * @return string
     */
    public function makeRefreshToken($exp = 604800);

    /**
     * 刷新token
     *
     * @param string $refreshToken
     * @param int    $exp
     *
     * @return mixed
     */
    public function refreshToken(string $refreshToken, $exp = 604800);

    /**
     * 注销
     * @return mixed
     */
    public function logout();
}
