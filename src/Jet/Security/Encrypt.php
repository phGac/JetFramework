<?php

namespace Jet\Security;

class Encrypt
{
    /**
     * @param string $original
     * @param string $secret
     * @param string $method
     * @return string
     */
    static function encrypt($original, $secret, $method = 'aes-256-cbc')
    {
        $iv         = substr(sha1(mt_rand()), 0, 16);
        $password   = sha1($secret);
        $salt       = sha1(mt_rand());
        $saltWithPassword = hash('sha256', $password.$salt);
        $encrypted = openssl_encrypt($original, $method, "$saltWithPassword", null, $iv);
        return "$iv:$salt:$encrypted";
    }

    /**
     * @param string $encrypted
     * @param string $secret
     * @param string $method
     * @return false|string
     */
    static function decrypt($encrypted, $secret, $method = 'aes-256-cbc')
    {
        $password = sha1($secret);
        $components = explode( ':', $encrypted );
        $iv = $components[0];
        $salt = hash('sha256', $password.$components[1]);
        $encrypted_msg = $components[2];

        $decrypted_msg = openssl_decrypt(
            $encrypted_msg, $method, $salt, null, $iv
        );

        if ( $decrypted_msg === false )
            return false;

        $msg = substr( $decrypted_msg, 41 );
        return $decrypted_msg;
    }

    /**
     * @param string $original
     * @return false|string|null
     */
    static function hashMake($original)
    {
        return password_hash($original, PASSWORD_DEFAULT);
    }

    /**
     * @param string $original
     * @param string $hash
     * @return bool
     */
    static function hashVerify($original, $hash)
    {
        return password_verify($original, $hash);
    }
}