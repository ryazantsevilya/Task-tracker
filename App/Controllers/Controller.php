<?php
namespace App\Controllers;

class Controller
{
    public function render ($viewName, array $params = [])
    {
        $rootPath = dirname(__DIR__);

        $viewPath =  $rootPath.DIRECTORY_SEPARATOR.'Views'.DIRECTORY_SEPARATOR;
        
        $loader = new \Twig\Loader\FilesystemLoader($viewPath);
        
        $twig = new \Twig\Environment($loader);

        $twig->addGlobal('session', $_SESSION);
        echo $twig->render("$viewName.html", $params);  
    }
}