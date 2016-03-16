<?php

/*
 * This file is part of the SampleApi
 *
 * Copyright (C) 2016 サンプル
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\SampleApi\ServiceProvider;

require_once(__DIR__ . '/../vendor/autoload.php');

use Swagger\Annotations as SWG;
use Eccube\Application;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Plugin\SampleApi\Form\Type\SampleApiConfigType;
use Plugin\SampleApi\Form\Type\ApiClientType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;


class SampleApiServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
        $ep = $app['controllers_factory'];
        $ep->match('/OAuth2/token', 'Plugin\SampleApi\Controller\OAuth2\OAuth2Controller::token')->bind('oauth2_server_token');
        $ep->match('/OAuth2/tokeninfo', 'Plugin\SampleApi\Controller\OAuth2\OAuth2Controller::tokeninfo')->bind('oauth2_server_tokeninfo');

        $ep->match('/'.trim($app['config']['admin_route'], '/').'/OAuth2/authorize', 'Plugin\SampleApi\Controller\OAuth2\OAuth2Controller::authorize')->bind('oauth2_server_authorize');
        $app->mount('/', $ep);

        $app['api.version'] = "v1";
        $app['api.endpoint'] = "/api";

        $app->register(new \JDesrosiers\Silex\Provider\SwaggerServiceProvider(), array(
            "swagger.srcDir" => __DIR__ . "/../../../../vendor/zircote/swagger-php/library",
            "swagger.servicePath" => __DIR__ . "/",
            "swagger.apiVersion" => $app['api.version'],
        ));

        // プラグイン用設定画面
        // $app->match('/' . $app['config']['admin_route'] . '/plugin/SampleApi/config', 'Plugin\SampleApi\Controller\ConfigController::index')->bind('plugin_SampleApi_config');

        /**
         * @SWG\Resource(basePath="/api/v1",resourcePath="/api/v1")
         */
        $c = $app['controllers_factory'];

        /**
         * @SWG\Api(
         *     path="/products",
         *     @SWG\Operations(
         *         @SWG\Operation(
         *             method="GET",
         *             nickname="products"
         *          )
         *     )
         * )
         */
        $c->get('/products', 'Plugin\SampleApi\Controller\SampleApiController::index')->bind('plugin_SampleApi_hello');

        $app->mount($app["api.endpoint"].'/'.$app["api.version"], $c);


        // Form
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new SampleApiConfigType($app);
            $types[] = new ApiClientType($app['config']);
            return $types;
        }));

        // Form Extension

        // Repository

        // Service

        // // メッセージ登録
        // $app['translator'] = $app->share($app->extend('translator', function ($translator, \Silex\Application $app) {
        //     $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());
        //     $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
        //     if (file_exists($file)) {
        //         $translator->addResource('yaml', $file, $app['locale']);
        //     }
        //     return $translator;
        // }));

        // load config
        // $conf = $app['config'];
        // $app['config'] = $app->share(function () use ($conf) {
        //     $confarray = array();
        //     $path_file = __DIR__ . '/../Resource/config/path.yml';
        //     if (file_exists($path_file)) {
        //         $config_yml = Yaml::parse(file_get_contents($path_file));
        //         if (isset($config_yml)) {
        //             $confarray = array_replace_recursive($confarray, $config_yml);
        //         }
        //     }

        //     $constant_file = __DIR__ . '/../Resource/config/constant.yml';
        //     if (file_exists($constant_file)) {
        //         $config_yml = Yaml::parse(file_get_contents($constant_file));
        //         if (isset($config_yml)) {
        //             $confarray = array_replace_recursive($confarray, $config_yml);
        //         }
        //     }

        //     return array_replace_recursive($conf, $confarray);
        // });

        // ログファイル設定
        $app['monolog.SampleApi'] = $app->share(function ($app) {

            $logger = new $app['monolog.logger.class']('plugin.SampleApi');

            $file = $app['config']['root_dir'] . '/app/log/SampleApi.log';
            $RotateHandler = new RotatingFileHandler($file, $app['config']['log']['max_files'], Logger::INFO);
            $RotateHandler->setFilenameFormat(
                'SampleApi_{date}',
                'Y-m-d'
            );

            $logger->pushHandler(
                new FingersCrossedHandler(
                    $RotateHandler,
                    new ErrorLevelActivationStrategy(Logger::INFO)
                )
            );

            return $logger;
        });

    }

    public function boot(BaseApplication $app)
    {



    }
}
