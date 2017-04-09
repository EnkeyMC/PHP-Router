<?php 
require_once 'router/Router.php';
require_once 'router/base_controller/BaseController.php';
require_once 'test_controllers/controller1.php';

Router::registerControllers(['test' => Controller1::class]);
Router::registerControllers(['test' => Controller1::class], "admin");
Router::registerControllers(['' => Controller1::class]);
Router::registerRoute('/<view>/<senior>/');
Router::registerRoute("/<controller>/<action>/<id>");
Router::registerRoute("/admin/<controller>/<view>");
Router::registerRoute('/<controller:test>/<task>/');
Router::registerRoute("<controller>");

var_dump(Router::route());

?>