<?php


namespace App;


class Pre
{
    public static function print(mixed $data): void
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

    public static function dump(mixed $data): void
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
}