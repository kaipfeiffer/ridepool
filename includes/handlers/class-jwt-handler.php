<?php

namespace Loworx\Ridepool;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @class JWT_Singleton
 * 
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */
class JWT_Handler
{
    /**
     * SECRET
     * 
     * Fallback-Secret, falls keine Daten aus der Wordpress-Installation ausgelesen werden können
     */
    const SECRET = 'W42#FIm%huEEQPNI%NZ4CVr&$BqAgFy5%8cmviGt';


    /**
     * $algorithm.
     *
     * Der Algorithmus, mit dem der Hash des Tokens verschlüsselt wird
     * 
     * @since    1.0.0
     */
    protected static $algorithm = 'sha512';


    /**
     * Die Instanz der Klasse.
     *
     * Die Instanz-Variable muss in jedem Child-Element neu 
     * definiert werden, damit jes Singleton mit seiner eigenen Instanz
     * arbeitet
     * 
     * @since    1.0.0
     */
    protected static $instance = null;


    /**
     * $secret.
     *
     * Der Algorithmus, mit dem der Hash des Tokens verschlüsselt wird
     * 
     * @since    1.0.0
     */
    protected static $secret   = null;


    /**
     * Die Klasse.
     *
     * Die Klasse muss in jedem Child-Element neu mit der magischen 
     * Konstanten __CLASS__ belegt werden, damit bei den statischen
     * Methoden die richtigen Referenzierungen genutzt werden.
     * 
     * @var string
     * 
     * @since    1.0.0
     */
    protected static $self      = __CLASS__;


    /**
     * $issuer.
     *
     * Der Anbieter des Dienstes
     * 
     * @since    1.0.0
     */
    protected static $issuer;


    /**
     * decode
     * 
     * Kodiert den übergebenen String in base64
     * 
     * @param   string
     * @since    1.0.0
     */
    protected static function decode($data)
    {
        return base64_decode($data);
    }


    /**
     * encode
     * 
     * Kodiert den übergebenen String in base64
     * 
     * @param   string
     * @since    1.0.0
     */
    protected static function encode($data)
    {
        // return base64_encode($data);
        // Das str_replace macht das Token URL-kompatibel
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }


    /**
     * get_issuer
     * 
     * gibt den Herausgeber des Tokens zurück
     * 
     * @return  string
     * @since    1.0.0
     */
    protected static function get_issuer()
    {
        if (!static::$issuer) {
            static::$issuer = $_SERVER['SERVER_NAME'];
        }
        return static::$issuer;
    }


    /**
     * get_secret
     * 
     * Falls Wordpress-Konstanten für Keys gesetzt sind, diese zum
     * verschlüsseln des Hashes nutzen
     * 
     * @return  string
     * @since    1.0.0
     */
    protected static function get_secret()
    {
        if (!static::$secret) {
            if (defined('\SECURE_AUTH_SALT')) {
                $secret = \SECURE_AUTH_SALT;
            }
            if (defined('\SECURE_AUTH_KEY')) {
                $secret .= \SECURE_AUTH_KEY;
            }
            static::$secret   = $secret ?? static::SECRET;
        }
        return static::$secret;
    }


    /**
     * decode_jwt
     * 
     * erzeugt ein JSON-Web-Token mit den übermittelten Daten
     * 
     * @param   array   die zu speichernden Daten
     * @param   integer die Gültigkeits-Dauer des Tokens, default 1 Tag
     * @since    1.0.0
     */
    public static function decode_jwt($jwt)
    {
        $result     = null;
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature)    = explode('.', $jwt);

        $json_header     = static::decode($base64UrlHeader);
        $json_payload    = static::decode($base64UrlPayload);
        $signature       = static::decode($base64UrlSignature);

        if ($json_header && $header = json_decode($json_header)) {
            $algorithm  = isset($header->alg) ? $header->alg : static::$algorithm;
        }

        // Schlüssel auslesen
        $secret = static::get_secret();

        if ($signature === hash_hmac($algorithm, $base64UrlHeader . '.' . $base64UrlPayload, $secret)) {
            $payload    = json_decode($json_payload);
            if ($payload->exp > time()) {
                $result = $payload->data;
            }
        }

        return $result;
    }


    /**
     * generate_jwt
     * 
     * erzeugt ein JSON-Web-Token mit den übermittelten Daten
     * 
     * @param   array   die zu speichernden Daten
     * @param   integer die Gültigkeits-Dauer des Tokens, default 1 Tag
     * @since    1.0.0
     */
    public static function generate_jwt($data, $validity = 60 * 60 * 24)
    {
        // Generierungsdatum
        $iat = time();

        $payload    = array(
            'data'  => $data,
            'id'    => uniqid(static::$issuer, true), // Unique ID
            'sub'   => 'SBU-Handout',                 // Subject
            'exp'   => $iat + $validity,            // Expiration date
            'iss'   => static::get_issuer(),         // issuer
            'iat'   => $iat,                        // issued at
        );

        // Create token header as a JSON string
        $json_header = json_encode(['typ' => 'JWT', 'alg' => static::$algorithm]);

        // Create token payload as a JSON string
        $json_payload = json_encode($payload);

        // Encode Header to Base64Url String
        $base64UrlHeader = static::encode($json_header);

        // Encode Payload to Base64Url String
        $base64UrlPayload = static::encode($json_payload);

        // Schlüssel auslesen
        $secret = static::get_secret();

        // Create Signature Hash
        $signature = hash_hmac(static::$algorithm, $base64UrlHeader . '.' . $base64UrlPayload, $secret);

        // Encode Signature to Base64Url String
        $base64UrlSignature = static::encode($signature);

        // Create JWT
        $jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;

        return $jwt;
    }
}
