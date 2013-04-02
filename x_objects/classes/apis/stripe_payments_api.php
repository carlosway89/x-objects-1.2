<?php
/**
 *
 * API Web Component for managing functional connections to Stripe Payments.
 * Required to be Unit-testable for production-readiness.
 *
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 14/02/13
 * Time: 11:38 AM
 */
class stripe_payments_api implements unit_testable{
    private $config = null;
    private $error = "";            // last error from any operation
    private $mode = "test";         // mode of operation
    public function __construct(){
        global $container;
        $this->config = (object)$container->config->stripe_payments;
        if ( ! $this->config )
            throw new ObjectNotInitializedException("Please make sure your Stripe Payments settings are specified in /app/xml/x-objects.xml within the element 'stripe_payments'");
        $this->mode = $this->config->mode?$this->config->mode:$this->mode;
        Stripe::setApiKey((string)$this->config->test_secret_key);
    }
    public function test_publishable_key(){
        return (string) $this->config->test_publishable_key;
    }
    public function get_error(){
        return $this->error;
    }
    public function get_tests(){
        return array();
    }
    public function test_self(){
        return true;
    }
    public function test_all(){
        return true;
    }
    public function get_state(){
        return array();
    }
    // levy a charge
    public function charge($charge_data){
        return Stripe_Charge::create($charge_data);
    }
}
