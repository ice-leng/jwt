<?php

namespace Lengbin\Jwt;

interface JwtInterface
{
    /**
     * 生成token
     *
     * @param array $data
     *
     * @return string
     */
    public function generate(array $data): string;

    /**
     * 生成 刷新token
     *
     * @param string $token
     *
     * @return string
     */
    public function generateRefreshToken(string $token): string;

    /**
     * @param string $token
     *
     * @return array|null
     */
    public function verifyToken(string $token): ?array;

    /**
     * 刷新token
     *
     * @param string $refreshToken
     *
     * @return string
     */
    public function refreshToken(string $refreshToken): string;

    /**
     * 注销
     *
     * @param string $token
     *
     * @return bool
     */
    public function logout(string $token): bool;
}
