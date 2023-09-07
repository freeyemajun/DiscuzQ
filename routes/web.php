<?php
use App\Install\Controller as InstallController;
use \Discuz\Http\RouteCollection;
/**@var RouteCollection $route */
$route->withFrequency(function (RouteCollection $route){
    $route->get('/plugin_static/{plugin_name}/{module_name}/{file_path}', 'plugin.file', \App\Http\Controller\PluginFileController::class);
},10,30,5);
$route->get('/install', 'install.index', InstallController\IndexController::class);
$route->post('/install', 'install', InstallController\InstallController::class);
$route->get('/upgrade', 'upgrade', InstallController\UpgradeLogController::class);
