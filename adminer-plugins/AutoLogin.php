<?php

class AutoLogin extends \Adminer\Plugin
{
    private $env = [
        'USERNAME' => '',
        'PASSWORD' => '',
    ];

    public function __construct()
    {
        if (!file_exists('.env')) {
            return;
        }

        $env = file_get_contents('.env');
        $env = explode("\n", $env);
        $env = array_map('trim', $env);
        $env = array_filter($env);
        $env = array_map(function ($item) {
            return explode('=', $item);
        }, $env);

        $this->env = array_combine(array_column($env, 0), array_column($env, 1));
    }

    public function credentials()
    {
        $password = Adminer\get_password();
        $username = $_GET['username'] ?: $this->env['USERNAME'];
        $password = $password ?: $this->env['PASSWORD'];

        return [Adminer\SERVER, $username, $password];
    }

    public function login($login, $password)
    {
        return true;
    }
}
