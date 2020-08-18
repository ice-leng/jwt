<?php

namespace Lengbin\Jwt;

use EasySwoole\Jwt\Exception;
use EasySwoole\Jwt\Jwt;
use EasySwoole\Jwt\JwtObject;
use Lengbin\Helper\Util\SnowFlakeHelper;
use Lengbin\Helper\YiiSoft\Arrays\ArrayHelper;
use Lengbin\Jwt\Exception\ExpiredJwtException;
use Lengbin\Jwt\Exception\InvalidJwtException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class Token
 *
 * @package Lengbin\Jwt
 */
class Token implements JwtInterface
{
    protected $config;
    protected $cache;

    /**
     * Auth constructor.
     *
     * @param CacheInterface $cache
     * @param Config         $config
     */
    public function __construct(CacheInterface $cache, Config $config)
    {
        $this->cache = $cache;
        $this->config = $config;
    }

    /**
     * @return Jwt
     */
    protected function getJwt(): Jwt
    {
        return Jwt::getInstance()->setSecretKey($this->config->key);
    }

    /**
     * 生成token
     *
     * @param array $data
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function generate(array $data): string
    {
        $time = time();
        $json = json_encode($data);
        $id = $this->config->sso ? ArrayHelper::getValue($data, 'id', $json) : $time;
        $jti = md5($id);
        $jwtObject = $this->getJwt()->publish();
        $jwtObject->setAlg($this->config->alg)->setExp(($time + $this->config->exp))->setIat($time)->setJti($jti)->setData($json);
        $token = $jwtObject->__toString();
        if ($this->config->sso) {
            $this->cache->set($jti, $token, $this->config->exp);
        }
        return $token;
    }

    /**
     * 生成 刷新token
     *
     * @param string $token
     *
     * @return string
     * @throws InvalidJwtException|ExpiredJwtException|InvalidArgumentException
     */
    public function generateRefreshToken(string $token): string
    {
        $ttl = $this->config->ttl;
        $refreshToken = (string)SnowFlakeHelper::make(1, 1);
        $data = $this->verify($token)->getData();
        $this->cache->set($refreshToken, $data, $ttl);
        // token 与  刷新token 关联
        $this->cache->set($token, $refreshToken, $this->config->exp);
        return $refreshToken;
    }

    /**
     * 获得 JwtObject
     *
     * @param string $token
     *
     * @return JwtObject|null
     * @throws InvalidJwtException|ExpiredJwtException|InvalidArgumentException
     */
    private function verify(string $token): ?JwtObject
    {
        try {
            $jwtObject = $this->getJwt()->decode($token);
            $status = $jwtObject->getStatus();
            // 无效
            if ($status === -1) {
                throw new InvalidJwtException('Invalid Token');
            }
            // 过期
            if ($status === -2) {
                throw new ExpiredJwtException('Expired Token');
            }

            // 单点登录
            if ($this->config->sso) {
                $ssoToken = $this->cache->get($jwtObject->getJti());
                if ($ssoToken !== $token) {
                    throw new InvalidJwtException('Invalid Token');
                }
            }

            return $jwtObject;
        } catch (Exception $e) {
            throw new InvalidJwtException($e->getMessage());
        }
    }

    /**
     * @param string $token
     *
     * @return array|null
     * @throws ExpiredJwtException|InvalidJwtException|InvalidArgumentException
     */
    public function verifyToken(string $token): ?array
    {
        $data = $this->verify($token)->getData();

        // 判断 token 是否 和 刷新token 关联
        $refreshToken = $this->cache->get($token);
        if (empty($refreshToken)) {
            throw new InvalidJwtException('Invalid Token');
        }
        // 如果没有刷新token 表示 logout
        $result = $this->cache->get($refreshToken);
        if (empty($result)) {
            throw new InvalidJwtException('Invalid Token');
        }

        return json_decode($data, true);
    }

    /**
     * 刷新token
     *
     * @param string $refreshToken
     *
     * @return string
     * @throws InvalidArgumentException|InvalidJwtException
     */
    public function refreshToken(string $refreshToken): string
    {
        $data = $this->cache->get($refreshToken);
        if (empty($data)) {
            throw new InvalidJwtException('Invalid Refresh Token');
        }
        $data = json_decode($data, true);
        if (empty($data)) {
            throw new InvalidJwtException('Invalid Token Data');
        }
        $token = $this->generate($data);
        // token 与  刷新token 关联
        $this->cache->set($token, $refreshToken, $this->config->exp);
        return $token;
    }

    /**
     * 注销
     *
     * @param string $token
     *
     * @return bool
     * @throws ExpiredJwtException|InvalidJwtException|InvalidArgumentException
     */
    public function logout(string $token): bool
    {
        $delete = [$token];
        $this->verifyToken($token);
        // 单点登录
        if ($this->config->sso) {
            $delete[] = $this->verify($token)->getJti();
        }
        // 刷新token
        $refreshToken = $this->cache->get($token);
        if (!empty($refreshToken)) {
            $delete[] = $refreshToken;
        }
        $this->cache->delete(...$delete);
        return true;
    }

}