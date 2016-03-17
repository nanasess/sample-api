<?php

/*
 * This file is part of the SampleApi
 *
 * Copyright (C) 2016 サンプル
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\SampleApi;

use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{

    /**
     * @var string コピー元リソースディレクトリ
     */
    private $origin;

    /**
     * @var string コピー先リソースディレクトリ
     */
    private $target;

    /**
     * @var array エンティティクラスの配列
     */
    private $classes;

    public function __construct()
    {
        // コピー元のディレクトリ
        $this->origin = __DIR__ . '/Resource/swagger-ui';
        // コピー先のディレクトリ
        $this->target = __DIR__ . '/../../../html/plugin/swagger-ui';

        $this->classes = array(
            '\Plugin\SampleApi\Entity\OAuth2\AuthorizationCode',
            '\Plugin\SampleApi\Entity\OAuth2\User',
            '\Plugin\SampleApi\Entity\OAuth2\Scope',
            '\Plugin\SampleApi\Entity\OAuth2\OpenID\UserInfo',
            '\Plugin\SampleApi\Entity\OAuth2\OpenID\PublicKey',
            '\Plugin\SampleApi\Entity\OAuth2\OpenID\UserInfoAddress',
            '\Plugin\SampleApi\Entity\OAuth2\RefreshToken',
            '\Plugin\SampleApi\Entity\OAuth2\AccessToken',
            '\Plugin\SampleApi\Entity\OAuth2\Client'
        );
    }

    /**
     * プラグインインストール時の処理
     *
     * @param $config
     * @param $app
     * @throws \Exception
     */
    public function install($config, $app)
    {
        // リソースファイルのコピー
        $this->copyAssets();
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migrations', $config['code']);
    }

    /**
     * プラグイン削除時の処理
     *
     * @param $config
     * @param $app
     */
    public function uninstall($config, $app)
    {
        // リソースファイルの削除
        $this->removeAssets();
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migrations', $config['code'], 0);
    }

    /**
     * プラグイン有効時の処理
     *
     * @param $config
     * @param $app
     * @throws \Exception
     */
    public function enable($config, $app)
    {
        // リソースファイルのコピー
        $this->copyAssets();
    }

    /**
     * プラグイン無効時の処理
     *
     * @param $config
     * @param $app
     */
    public function disable($config, $app)
    {
    }

    public function update($config, $app)
    {
    }


    /**
     * リソースファイル等をコピー
     */
    private function copyAssets()
    {
        $file = new Filesystem();
        $file->mirror($this->origin, $this->target);
    }

    /**
     * コピーしたリソースファイルなどを削除
     */
    private function removeAssets()
    {
        $file = new Filesystem();
        $file->remove($this->target);
    }
}
