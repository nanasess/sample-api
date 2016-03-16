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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\DomCrawler\Crawler;

class SampleApiEvent
{

    /** @var  \Eccube\Application $app */
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onAppRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->getMethod() === "OPTIONS") {
            $response = new Response();
            $response->headers->set("Access-Control-Allow-Origin","*");
            $response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
            $response->headers->set("Access-Control-Allow-Headers","Content-Type");
            $response->setStatusCode(200);
            $response->send();
        }

        //accepting JSON
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        error_log('onAppRequest');
    }

    public function onAppController(FilterControllerEvent $event)
    {
        error_log('onAppController');
    }

    public function onAppResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $response->headers->set("Access-Control-Allow-Origin","*");
        $response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
        error_log('onAppResponse');
    }

    public function onAppException(GetResponseForExceptionEvent $event)
    {
        error_log('onAppException');
    }

    public function onAppTerminate(PostResponseEvent $event)
    {
        error_log('onAppTerminate');
    }

    public function onFrontRequest(GetResponseEvent $event)
    {
        error_log('onFrontRequest');
    }

    public function onFrontController(FilterControllerEvent $event)
    {
        error_log('onFrontController');
    }

    public function onFrontResponse(FilterResponseEvent $event)
    {
        error_log('onFrontResponse');
    }

    public function onFrontException(GetResponseForExceptionEvent $event)
    {
        error_log('onFrontException');
    }

    public function onFrontTerminate(PostResponseEvent $event)
    {
        error_log('onFrontTerminate');
    }

    public function onAdminRequest(GetResponseEvent $event)
    {
        error_log('onAdminRequest');
    }

    public function onAdminController(FilterControllerEvent $event)
    {
        error_log('onAdminController');
    }

    public function onAdminResponse(FilterResponseEvent $event)
    {
        error_log('onAdminResponse');
    }

    public function onAdminException(GetResponseForExceptionEvent $event)
    {
        error_log('onAdminException');
    }

    public function onAdminTerminate(PostResponseEvent $event)
    {
        error_log('onAdminTerminate');
    }

    public function onRouteRequest(GetResponseEvent $event)
    {
        error_log('onRouteRequest');
    }

    public function onRouteController(FilterControllerEvent $event)
    {
        error_log('onRouteController');
    }

    public function onRouteResponse(FilterResponseEvent $event)
    {
        error_log('onRouteResponse');
    }

    public function onRouteException(GetResponseForExceptionEvent $event)
    {
        error_log('onRouteException');
    }

    public function onRouteTerminate(PostResponseEvent $event)
    {
        error_log('onRouteTerminate');
    }
    public function onRouteAdminMemberResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $html = $response->getContent();
        $crawler = new Crawler($html);
        $oldElement= $crawler->filter('#common_button_box__insert_button');
        $oldHtml= $oldElement->html();
        $newHtml= $oldHtml.'<button class="btn btn-primary btn-block btn-lg" onclick="window.location.href=\'api\';">APIクライアント一覧</button>';
        $html = $crawler->html();
        $html =str_replace($oldHtml, $newHtml, $html);
        $response->setContent($html);
        $event->setResponse($response);
    }
}
