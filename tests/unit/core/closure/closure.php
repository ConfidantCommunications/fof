<?php

/**
 * We can use instances of this class in order to create "on-the-fly" methods, so we can inject our code
 * inside a function with the name the System Under Test is expecting, for example:
 *
 * $object = new FOFClosure();
 * $object->foo = function(){ return "Hello World!"};
 *
 * $object->foo() // Returns "Hello World!"
 *
 * See: http://stackoverflow.com/a/2938020/485241
 */
class FOFClosure
{
    /**
     * Assigns callback functions to the class, the $methods array should be an associative one, where
     * the keys are the method names, while the values are the closure functions, e.g.
     *
     * array(
     *    'foobar' => function(){ return 'Foobar'; }
     * )
     *
     * @param array $methods
     */
    public function __construct(array $methods = array())
    {
        foreach($methods as $method => $function)
        {
            $this->$method = $function;
        }
    }

    public function __call($method, $args)
    {
        if (isset($this->$method))
        {
            $func = $this->$method;

            // Let's pass an instance of ourself, so we can manipulate other closures
            array_unshift($args, $this);

            return call_user_func_array($func, $args);
        }
    }

    /**
     * I have to hardcode this function since sometimes we do a get_class_methods check and that won't
     * trigger __call
     *
     * @return mixed
     */
    public function getState()
    {
        if(isset($this->_getState))
        {
            $func = $this->_getState;

            return call_user_func_array($func, array());
        }
    }

    public function getErrors()
    {
        if(isset($this->_getErrors))
        {
            $func = $this->_getErrors;

            return call_user_func_array($func, array());
        }
    }
}