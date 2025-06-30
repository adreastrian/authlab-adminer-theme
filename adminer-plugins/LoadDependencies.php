<?php

class LoadDependencies extends \Adminer\Plugin
{
    public function head()
    {
        echo '<script' . \Adminer\nonce() . 'src="/js/jquery.min.js"></script>';

        echo '<script' . \Adminer\nonce() . 'src="/js/index.js"></script>';
    }
}
