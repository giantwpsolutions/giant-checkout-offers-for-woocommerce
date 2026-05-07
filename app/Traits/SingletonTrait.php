<?php 

namespace GiantCheckoutOffers\Traits;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Singleton Instance Trait
 */
trait SingletonTrait{
    /**
     * The single instance of the class
     */
    private static $instance = null;

    /**
     * Returns the single instance of the class
     *
     * @return mixed
     */
    public static function instance() {
        if ( null === static::$instance ) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {}  
}