<?php

namespace Jet\Security;

class Encrypt
{
    static function encrypt(string $original, string $secret, $method = 'aes-256-cbc')
    {
        $iv         = substr(sha1(mt_rand()), 0, 16);
        $password   = sha1($secret);
        $salt       = sha1(mt_rand());
        $saltWithPassword = hash('sha256', $password.$salt);
        $encrypted = openssl_encrypt($original, $method, "$saltWithPassword", null, $iv);
        return "$iv:$salt:$encrypted";
    }

    static function decrypt(string $encrypted, string $secret, $method = 'aes-256-cbc')
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

    static function hashMake(string $original)
    {
        return password_hash($original, PASSWORD_DEFAULT);
    }

    static function hashVerify(string $original, string $hash)
    {
        return password_verify($original, $hash);
    }
}