<?php

/**
 * Custom item object class. Configures magic getters and setters.
 * 
 * @package Postheadincludes
 * @author Rick Buczynski <richard.buczynski@gmail.com>
 * @version 0.2.1
 * 
 */

class WP_Plugins_Postheadincludes_Item {
    
    private $_data=array();
    public static $_itemIdPrefix='item_';
    
    /**
     * Prepare defaults and constructor values.
     * 
     * @return \WP_Plugins_Postheadincludes_Item
     */
    public function __construct() {
        $args=func_get_args();
        
        if(empty($args[0]))
            $args[0]=array();
        
        $newId=self::$_itemIdPrefix.substr(md5(time()),-6);
        
        $this->_data=array_merge(array(
            'id'            => $newId,
            'post_id'       => null,
            'handle'        => $newId,
            'src'           => '',
            'deps'          => '',
            'ver'           => '',
            'in_footer'     => false,
            'media'         => '',
            'localization'  => '',
            'condition'     => ''
        ),(array) $args[0]);
        
        return $this;
    }
    
    /**
     * Setup for magic methods.
     * 
     * @param type $method
     * @param type $args
     * @return type
     */
    public function __call($method,$args) {
        switch(substr($method,0,3)) {
            case 'get':
            $key=$this->_underscore(substr($method,3));
            $data=$this->getData($key,isset($args[0])?$args[0]:null);
            return $data;
            
            case 'set':
            $key=$this->_underscore(substr($method,3));
            $result=$this->setData($key,isset($args[0])?$args[0]:null);
            return $result;
        }
    }
    
    /**
     * Magic getter.
     * 
     * @param type $name
     * @return type
     */
    public function __get($name) {
        $key=$this->_underscore($name);
        return $this->getData($key);
    }
    
    /**
     * Magic setter.
     * 
     * @param type $name
     * @param type $value
     * @return \WP_Plugins_Postheadincludes_Item
     */
    public function __set($name,$value) {
        $key=$this->_underscore($name);
        $this->setData($key,$value);
        
        return $this;
    }
    
    /**
     * Helper for magic methods.
     * 
     * @param type $name
     * @return type
     */
    public function _underscore($name) {
        return strtolower(preg_replace('/(.)([A-Z])/',"$1_$2",$name));
    }
    
    /**
     * Retrieve a stored item by its key.
     * 
     * @param type $key
     * @return boolean
     */
    public function getData($key=null) {
        if(is_null($key))
            return $this->_data;
        
        if(isset($this->_data[$key]))
            return $this->_data[$key];
        
        return false;
    }
    
    /**
     * Special handling for dependencies field.
     * @see http://codex.wordpress.org/Function_Reference/wp_enqueue_script
     * 
     * @return mixed
     */
    public function getDeps() {
        if((implode(',',$this->getData('deps')))=='')
            return false;
        
        return $this->getData('deps');
    }
    
    /**
     * Special handling for footer placement field.
     * @see http://codex.wordpress.org/Function_Reference/wp_enqueue_script
     * 
     * @return mixed
     */
    public function getInFooter() {
        if((boolean) $this->getData('in_footer')===true)
            return true;
        
        return false;
    }
    
    /**
     * Set a stored item's value.
     * 
     * @param type $key
     * @param type $value
     * @return \WP_Plugins_Postheadincludes_Item
     */
    public function setData($key,$value) {
        $this->_data[$key]=$value;
        
        return $this;
    }
    
}