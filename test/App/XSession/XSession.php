<?php

/**
 * Encrypted Session based handler using Open SSL with AES-256-CBC 
 *
 * @author    Olubodun Agbalaya (s.stackng@gmail.com)
 * @version    GIT: 1.0.0
 * @copyright 2017 Olubodun Agbalaya
 * @license MIT License
 */

namespace solutionstack\XSession;

use SessionHandler;

class XSession extends SessionHandler {

    protected $key, $name, $cookie , $ttl;

    const SAVE_PATH = (__DIR__ ) . "/sess";

    /**
     * Constructor
     * 
     * @param string $name Optional parameter indicating the name to identify the session and also used as the cookie name.
     * @param string $liftime Optional parameter indicating the lifetime of the session in seconds, defaults to 0 (i.till the browser tab is closed )
     * 
     * @throws Error If the OpenSSLextension isn't loaded
     * 
     */
    public function __construct($name = 'X_SESSION', $lifetime = 0) {
        
        if(!\function_exists("openssl_encrypt")){
            
            throw new Error("This class requires the OpenSSL extension!");
        }
        
        
        $this->key = \substr(\hash('sha256', $name), 0, 32);
        $this->name = $name;
        $this->ttl = $lifetime;
        $this->cookie = [
            'lifetime' =>  $this->ttl,
            'path' => "/",
            'domain' => "." . \preg_replace('/www\./i', '', $_SERVER['SERVER_NAME']),
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true
        ];
        $this->setup();
    }

    //set things up
    protected function setup() {
        
        \ini_set('session.use_cookies', 1);
        \ini_set('session.use_only_cookies', 1);
        \ini_set('session.save_path', \realpath(self::SAVE_PATH));
        \session_name($this->name);
        \session_set_cookie_params(
                $this->cookie['lifetime'], $this->cookie['path'], $this->cookie['domain'], $this->cookie['secure'], $this->cookie['httponly']
        );
    }

    //is the session already started    

    protected function sessionStarted() {

        return \session_id() !== "" || \session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Call this method to initially start the session
     * 
     */
    public function start() {
        if (!$this->sessionStarted()) {
            if (\session_start()) {

                $hash = md5($_SERVER['HTTP_USER_AGENT'] . "---|---" . $this->name);
                $_SESSION['_fingerprint'] = $hash;

                return mt_rand(0, 4) === 0 ? $this->refresh() : true; // 1/5, regenerate sid
            }
        }
        return false;
    }

    /**
     * Call this method to resume a previously started session
     * 
     */
    public function resume() {
       
        if (!isset($_COOKIE) || !\array_key_exists($this->name, $_COOKIE)) {

            //coookie prob expired
            $this->forget();
            return false;
        }



        if (!$this->sessionStarted()) {

            if (\session_start()) {
                $this->refresh();
            }
        }
        if (!$this->isFingerprint()) { //UA doesnt match
            $this->forget();
            return false;
        }

        return true;
    }

    /**
     * End the session
     * 
     */
    public function end() {
        return $this->forget();
    }
    /**
     * End the session
     * 
     */
    public function getSID() {
        return \session_id();
    }

    protected function forget() {

        if (!$this->sessionStarted()) {
            if (\session_start()) {
                
            }
        }
      
        \setcookie(
                $this->name, '', time() - 42000, $this->cookie['path'], $this->cookie['domain'], $this->cookie['secure'], $this->cookie['httponly']
        );


        unset($_SESSION);


        \session_destroy();
        return true;
    }

    protected function refresh() {
        return session_regenerate_id(true);
    }

    public function open($save_path, $session_name) {

        return parent::open($save_path, $session_name);
    }

    public function read($id) {
        return (string) @openssl_decrypt(parent::read($id), "aes-256-cbc", $this->key);
    }
    
    

    public function write($id, $data) {
        return parent::write($id, @openssl_encrypt($data, "aes-256-cbc", $this->key));
    }

    protected function isFingerprint() {

        $hash = \md5($_SERVER['HTTP_USER_AGENT'] . "---|---" . $this->name);
        if (isset($_SESSION['_fingerprint'])) {
            return $_SESSION['_fingerprint'] === $hash;
        }

        return false;
    }

    /**
     * Get a previously set value associated with this session
     * @param string|integer $key The key to be used in retrieving a value
     * 
     * @return mixed|boolean Returns the value set with $key or an empty string if the key is not found
     * *@throws DomainException If there are no active sessions
     */
    public function get($key) {

        if (!$this->sessionStarted()) {
            throw new \DomainException("Cant retrieve value from NULL session");
        }

        if (\array_key_exists($key, $_SESSION)) {

            return $_SESSION[$key];
        }
        return '';
    }

    /**
     * Associates a key/value with this session
     * @param string|integer $key
     * @param mixed $value
     * @return boolean
     * @throws DomainException If there are no active sessions
     */
    public function put($key, $value) {

        if (!$this->sessionStarted()) {
            throw new \DomainException("Cant set value on NULL session");
        }

        $_SESSION[$key] = $value;

        return true;
    }

}
