<?php


namespace Jet\Security;


class Token
{
    /**
     * Generate a random string
     * @param int $length
     * @return string
     */
    static function random($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    static function csrf()
    {
        return md5(uniqid(mt_rand(), true));
    }
}