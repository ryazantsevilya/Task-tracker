<?php
namespace App\Models;

class Task {
    public $id;
    public $username;
    public $status;
    public $email;
    public $content;
    public $isChanged = false;
}