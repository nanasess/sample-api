<?php

namespace Plugin\SampleApi\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\HttpFoundationBridge\Response as BridgeResponse;

abstract class AbstractApiController
{
    protected function getServer(Application $app)
    {
        $entityManager = $app['orm.em'];
        $clientStorage  = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\Client');
        // $userStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\User');
        $accessTokenStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\AccessToken');
        $authorizationCodeStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\AuthorizationCode');

        // Pass the doctrine storage objects to the OAuth2 server class
        $server = new \OAuth2\Server(array(
            'client_credentials' => $clientStorage,
            // 'user_credentials'   => $userStorage,
            'access_token'       => $accessTokenStorage,
            'authorization_code' => $authorizationCodeStorage,
        ), array(
            'auth_code_lifetime' => 300,
            'refresh_token_lifetime' => 300,
        ));
        $server->addGrantType(new \OAuth2\GrantType\ClientCredentials($clientStorage));
        $server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($authorizationCodeStorage));
        return $server;
    }

    protected function verifyRequest(\OAuth2\Server $server, $scope_reuqired = null)
    {
        return $server->verifyResourceRequest(\OAuth2\Request::createFromGlobals(), new BridgeResponse(), $scope_reuqired);
    }
}
