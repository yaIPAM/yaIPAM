<?php
/**
 * core.php
 * Project: yaIPAM
 * User: ktammling
 * Date: 06.06.17
 * Time: 14:32
 */

namespace Framework;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class API
{
    public function handle(Request $request, $whoops, $tpl)
    {
        if (empty($request->query->get('url'))) {
            $url = "default";
        } else {
            $url = $request->query->get('url');
        }

        $urlArray = array();
        $urlArray = explode("/", $url);

        $controller = $urlArray[0];
        array_shift($urlArray);
        if (empty($urlArray[0])) {
            $action = "IndexAction";
        } else {
            $action = strtolower($request->getMethod()).ucfirst(strtolower($urlArray[0])).'Action';
        }
        array_shift($urlArray);
        $queryString = $urlArray;

        $namespace = '\APIController\\';
        $controllerName = $controller;
        $controller = ucwords($controller);
        $controller .= 'Controller';
        $controller = $namespace.$controller;
        if (method_exists($controller, $action)) {
            try {
                $dispatch = new $controller($controllerName, $action);
                call_user_func_array(array($dispatch, $action), $queryString);
            } catch (\Exception $e) {
                $whoops->handleException($e);
            }
        } else {
        	$json = array(
        		"code"  =>  404,
		        "message"   =>  "Method not found",
		        "controller"    =>  $controllerName,
		        "method"    =>  $request->getMethod(),
		        "action"    =>  $action,
	        );
            $response = new Response(json_encode($json), Response::HTTP_NOT_FOUND, array('Content-Type', 'text/json'));
            return $response->send();
        }
    }

    public static function unitTestAlive() {
        if ((defined('UNIT_TEST')
            && UNIT_TEST === true)) {
            return true;
        }

        return false;
    }
}