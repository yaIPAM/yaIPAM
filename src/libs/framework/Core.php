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

class Core
{
    public function __construct()
    {
    }


    public function handle(Request $request, $whoops, $tpl)
    {
        if (empty($request->query->get('url'))) {
            $url = "default";
        } else {
            $url = $request->query->get('url');
        }

        if ($_SESSION['login'] == false) {
            $url = "login/";
            $tpl->assign("S_LOGIN", false);
        } else {
            $tpl->assign("S_LOGIN", true);
        }

        $urlArray = array();
        $urlArray = explode("/", $url);

        $controller = $urlArray[0];
        array_shift($urlArray);
        if (empty($urlArray[0])) {
            $action = "IndexAction";
        } else {
            $action = $urlArray[0].'Action';
        }
        array_shift($urlArray);
        $queryString = $urlArray;

        $namespace = '\Controller\\';
        $controllerName = $controller;
        $controller = ucwords($controller);
        $model = rtrim($controller, 's');
        $controller .= 'Controller';
        $controller = $namespace . $controller;
        if (method_exists($controller, $action)) {
            try {
                $dispatch = new $controller($controllerName, $action);
                call_user_func_array(array($dispatch, $action), $queryString);
            } catch (\Exception $e) {
                $whoops->handleException($e);
            }
        } else {
            $dispatch = new \Controller\ErrorController('\Controller\ErrorController', 'NotfoundAction');
            call_user_func_array(array($dispatch, 'NotFoundAction'), $queryString);
        }
    }
}