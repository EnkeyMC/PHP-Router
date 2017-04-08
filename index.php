<?php 
require_once 'router/Router.php';
require_once 'router/base_controller/BaseController.php';
require_once 'test_controllers/controller1.php';

Router::registerControllers(['test' => Controller1::class]);
Router::registerRoute('/<controller:test>/<task>/');
Router::registerRoute('/<controller>/<view>/<id>');

Router::route();

?>