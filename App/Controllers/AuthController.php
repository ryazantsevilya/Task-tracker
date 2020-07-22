<?php
namespace App\Controllers;
use App\Models\Task;
use Rakit\Validation\Validator;

class AuthController extends Controller
{
    private function isValidUser($user, $pass)
    {
        if ($user === 'admin' && $pass === '123') {
            return TRUE;
        }
        return FALSE;
    }

    public function loginAction (){
        if (!empty($_POST['userlogin']) && !empty($_POST['pass'])) {
            if ($this->isValidUser($_POST['userlogin'], $_POST['pass'])) {
                $_SESSION['userlogin'] = $_POST['userlogin'];
                $_SESSION['auth'] = true; 
                header('Location: /');
                exit();
            }
            else
            {
                $_SESSION['userlogin'] = FALSE;
                return $this->render('login',['errors'=> ['Incorrect login']]);
            }
        } else {
            return $this->render('login',['errors'=> ['Incorrect login']]);
        }
    }

    public function loginPage(){
        return $this->render('login');
    }

    public function logoutAction(){
        session_destroy();
        header('Location: /');
        exit();
    }
}