<?php
/*
 * Plugin Name: Post Head Includes
 * Plugin URI: 
 * Description: Properly enqueue scripts and stylesheets into the page head per post.
 * Version: 0.2.1
 * Author: Rick Buczynski
 * Author URI: richard.buczynski@gmail.com
 * License: GPLv2
 * 
 * @package Postheadincludes
 * @author Rick Buczynski <richard.buczynski@gmail.com>
 * @version 0.2.1
*/

// Include the base item class
include_once 'item.php';

$_postheadincludes=new WP_Plugins_Postheadincludes();

// If in back-end, load the meta class
if(is_admin())
    include_once 'post-meta.php';

// Hook into WP
add_action('wp_head',array(&$_postheadincludes,'enqueueConditionalItems'),10000);
add_action('wp_enqueue_scripts',array(&$_postheadincludes,'enqueueItems'),10000);
add_action('wp_footer',array(&$_postheadincludes,'printLocalizations'),10000);

/**
 * Main plugin class for adding items to the page head.
 */
class WP_Plugins_Postheadincludes {
    
    private $_data=array();
    /**
     * @var WP_Plugins_Postheadincludes_Metabox
     */
    private $_metabox;
    private $_postId;
    
    protected static $_defaultEnqueueFunction='wp_enqueue_script';
    protected static $_wpOptionName='plugin_postheadincludes';
    
    /**
     * Retrieve plugin options data.
     */
    public function __construct() {
        $this->_data=$this->_prepareData();
    }
    
    /**
     * Return the enqueue function name from the item type.
     * 
     * @param WP_Plugins_Postheadincludes_Item $item
     * @return string
     */
    protected function _getEnqueueFunction(WP_Plugins_Postheadincludes_Item $item) {
        $functionName='wp_enqueue_'.strtolower($item->getType());
        
        if(function_exists($functionName))
            return $functionName;
        
        // type itself can be a full function name
        if(function_exists($item->getType()))
            return $functionName;
        
        return self::$_defaultEnqueueFunction;
    }
    
    /**
     * Return an ordered array of arguments based on the item type.
     * Array order should match expected order for `wp_enqueue_` functions.
     * 
     * @param WP_Plugins_Postheadincludes_Item $item
     * @return array
     */
    protected function _getEnqueueFunctionArgs(WP_Plugins_Postheadincludes_Item $item) {
        $args=array();
        switch($item->getType()) {
            case 'style':
                $args=array(
                    $item->getHandle(),
                    $item->getSrc(),
                    $item->getDeps(),
                    $item->getVer(),
                    $item->getMedia()
                );
                break;
            
            case 'script':
            default:
                $args=array(
                    $item->getHandle(),
                    $item->getSrc(),
                    $item->getDeps(),
                    $item->getVer(),
                    $item->getInFooter()
                );
                break;
        }
        
        return $args;
    }
    
    /**
     * Retrieve items in the collection for a specific post.
     * 
     * @param type $id
     * @return array
     */
    protected function _getItemsByPostId($id=null) {
        if(!($data=$this->getData()))
            return false;
        
        if(!$id)
            $id=$this->getPostId();
        
        if(isset($data['items'][$id]))
            return $data['items'][$id];
        else
            return array();
    }
    
    /**
     * Parse the plugin options data for easier handling.
     * 
     * @return \stdClass
     */
    protected function _prepareData() {
        $data=$this->loadData();
        
        $collection=array();
        if(isset($data['items'])) {
            foreach($data['items'] as $postId=>$_items) {
                $items=array();
                foreach($_items as $item)
                    $items[]=new WP_Plugins_Postheadincludes_Item($item);
                
                $collection[$postId]=$items;
            }
        }
        
        $data['items']=$collection;
        
        return $data;
    }
    
    /**
     * Add or update plugin data for this post.
     * 
     * @param mixed $_data
     * @param integer $id
     * @return \WP_Plugins_Postheadincludes
     */
    public function addData($_data,$id=null) {
        // Prepare the data
        $data=array();
        foreach($_data as $k=>$v) {
            if(is_array($v))
                $data[]=$v;
        }

        if($id>0)
            $this->_data['items'][$id]=$data;
        
        return $this;
    }
    
    /**
     * Support for IE conditional loading of items. Added in 0.2.0.
     */
    public function enqueueConditionalItems() {
        foreach($this->getItems($this->getPostId()) as $item) {
            if($item->getCondition()) {
                echo    "<!--[{$item->getCondition()}]>\n".
                        "\t{$this->getItemHtml($item)}\n".
                        "<![endif]-->\n";
            }
        }
    }
    
    /**
     * The whole point of the plugin: properly add an item to the page.
     */
    public function enqueueItems() {
        foreach($this->getItems($this->getPostId()) as $item) {
            if(!$item->getCondition()) {
                call_user_func_array(
                    $this->_getEnqueueFunction($item),
                    $this->_getEnqueueFunctionArgs($item)
                );
            }
        }
    }
    
    /**
     * Get the items collection.
     * 
     * @return array
     */
    public function getItems($id=null) {
        if($id)
            return $this->_getItemsByPostId($id);
        
        return $this->_data['items'];
    }
    
    /**
     * Generate the tags for an item when not using WP enqueue functions.
     * 
     * @param WP_Plugins_Postheadincludes_Item $item
     * @return string
     */
    public function getItemHtml(WP_Plugins_Postheadincludes_Item $item) {
        switch(strtolower($item->getType())) {
            case 'script':
                return '<script type="text/javascript" src="'.$item->getSrc().'"></script>';
            case 'style':
                return '<link rel="stylesheet" type="text/css" href="'.$item->getSrc().'" />';
        }
        
        return '';
    }
    
    /**
     * Get the meta-box.
     * 
     * @return \WP_Plugins_Postheadincludes_Metabox 
     */
    public function getMetabox() {
        return $this->_metabox;
    }
    
    /**
     * Get the plugin data.
     * 
     * @return mixed
     */
    public function getData() {
        return $this->_data;
    }
    
    /**
     * Get the post ID.
     * 
     * @return integer
     */
    public function getPostId() {
        if(!$this->_postId)
            $this->_postId=get_the_ID();
        
        return $this->_postId;
    }
    
    /**
     * Load the plugin data from the database.
     * 
     * @param boolean $raw
     * @return object
     */
    public function loadData($raw=false) {
        $data=get_option(self::$_wpOptionName);
        
        // For first-runs, to avoid PHP warnings
        if(!$data)
            return array('items'=>array());
        
        if($raw===true)
            return $data;
        
        return json_decode($data,true);
    }
    
    public function printLocalizations() {
        foreach($this->getItems($this->getPostId()) as $item) {
            if($item->getType()!='script' || !trim($item->getLocalization()))
                continue;
            
            echo "<script type='text/javascript'>\n".stripslashes($item->getLocalization())."\n</script>";
        }
    }
    
    public function removeData($id=null) {
        if(isset($this->_data['items'][$id]))
            unset($this->_data['items'][$id]);
        
        return $this;
    }
    
    /**
     * Save the plugin option data to the database.
     * 
     * @return \WP_Plugins_Postheadincludes
     */
    public function save() {
        update_option(self::$_wpOptionName,json_encode($this->getData()));
        
        return $this;
    }
    
    /**
     * Set the meta-box.
     * 
     * @param WP_Plugins_Postheadincludes_Metabox $metabox
     * @return \WP_Plugins_Postheadincludes
     */
    public function setMetabox(WP_Plugins_Postheadincludes_Metabox $metabox) {
        $this->_metabox=$metabox;
        
        return $this;
    }
    
    /**
     * Set the plugin data for this post.
     * 
     * @param mixed $data
     * @return \WP_Plugins_Postheadincludes
     */
    public function setData($data) {
        $this->_data=$data;
        
        return $this;
    }
    
}