<?php

declare(strict_types=1);

namespace Lengbin\Jwt;

use Lengbin\Helper\YiiSoft\ObjectHelper;
use Psr\SimpleCache\CacheInterface;

class Blacklist extends ObjectHelper
{
    /**
     * @var CacheInterface
     */
    public $cache;

    public $prefix = 'JWT-';

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
        if (!$this->cache instanceof CacheInterface) {
            throw new \InvalidArgumentException(get_class($this->cache) . ' must implement ' . CacheInterface::class);
        }
    }

    /**
     * token 加入缓存
     *
     * @param array $claims        [
     *                             jti, // 唯一id, uid
     *                             iat, //发布时间
     *                             exp, //到期时间
     *                             ]
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function add(array $claims)
    {
        $exp = $claims['exp'];
        $iat = $claims['iat'];
        $id = $claims['jti'];
        $claims['blacklist_register_at'] = time();
        $this->cache->set($this->prefix . $id, $claims, ($exp - $iat));
    }

    /**
     * 是否在黑名单中
     *
     * @param string $name
     * @param array  $claims
     *
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function has(string $name, array $claims)
    {
        $isOverdue = false;
        $data = $this->get($name);
        if (!empty($data) && !empty($claims)) {
            $isOverdue = ($data['blacklist_register_at'] - $claims['iat']) > 1;
        }
        return $isOverdue;
    }

    /**
     * 加入 黑名单
     *
     * @param $name
     *
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function join($name)
    {
        $claims = $this->get($name);
        if (empty($claims)) {
            return false;
        }
        $exp = $claims['exp'];
        $timeRemaining = $exp - time();
        if ($timeRemaining <= 0) {
            $this->delete($name);
            return false;
        }
        $claims['blacklist_register_at'] = time();
        $this->cache->set($this->prefix . $name, $claims, $timeRemaining);
        return true;
    }

    /**
     * 获得 缓存
     *
     * @param $name
     *
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get($name)
    {
        return $this->cache->get($this->prefix . $name);
    }

    /**
     * 移除 缓存
     *
     * @param $name
     *
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete($name)
    {
        return $this->cache->delete($this->prefix . $name);
    }

}
