<?php
/**
 *
 * Objects that are Unit-Testable provide various methods to test their state, publish
 * test methods to parent objects or invokers, and declare their state and any error
 * conditions.  It is recommended that any production components that are critical for
 * the site function implement this interface.
 *
 * User: "David Owen Greenberg" <code@x-objects.org>
 * Date: 13/02/13
 * Time: 10:19 AM
 */
interface unit_testable {
    /**
     * Run all tests
     * @return bool true if all tests ran successfully, false otherwise
     */
    public function test_all();

    /**
     * Run the self-test.  Used mainly to make sure the object is usable at all
     * @return bool true if the self-test ran successfully
     */
    public function test_self();

    /**
     * Get a list of method names for the object to run all tests
     * @return array list of test methods
     */
    public function get_tests();

    /**
     * Get the most recent error from tests (or from any operation)
     * @return string the error, or null/empty if none
     */
    public function get_error();

    /**
     * Get the current state of the Component.  Ideal after running tests, but for anytime
     * @return array description of object state
     */
    public function get_state();
}
