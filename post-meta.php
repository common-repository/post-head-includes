<?php

/**
 * Render and handle meta-box input on the post edit screen.
 * 
 * @package Postheadincludes
 * @author Rick Buczynski <richard.buczynski@gmail.com>
 * @version 0.2.1
 * 
 * @todo [low] Store data in a separate table (instead of wp_options)
 */

// We'll need the sources class for rendering drop-down options.
include_once 'sources.php';

// Hook into WP
$_postheadincludes_metabox=new WP_Plugins_Postheadincludes_Metabox($_postheadincludes);
add_action('add_meta_boxes',array(&$_postheadincludes_metabox,'add'));
add_action('save_post',array(&$_postheadincludes_metabox,'save'));

/**
 * Main meta-box class.
 */
class WP_Plugins_Postheadincludes_Metabox {
    
    private $_baseCssClass='postheadincludes';
    private $_formOptionGroup='postheadincludes';
    private $_plugin;
    
    /**
     * Link the metabox class to the plugin.
     * 
     * @param WP_Plugins_Postheadincludes $plugin
     * @return \WP_Plugins_Postheadincludes_Metabox
     */
    public function __construct($plugin=null) {
        if($plugin instanceof WP_Plugins_Postheadincludes) {
            $this->setPlugin($plugin);
            $this->getPlugin()->setMetabox($this);
        }
        
        return $this;
    }
    
    /**
     * Register the meta-box.
     * 
     * @return \WP_Plugins_Postheadincludes_Metabox
     */
    public function add() {
        $screens=array('post','page');
        
        foreach($screens as $screen)
            add_meta_box(
                'postheadincludes_metabox',
                __('Head Includes','postheadincludes'),
                array($this,'render'),
                $screen
            );
        
        return $this;
    }
    
    /**
     * Generate the advanced options HTML.
     * 
     * @param WP_Plugins_Postheadincludes_Item $item
     * @return string
     */
    public function getAdvancedOptionsHtml(WP_Plugins_Postheadincludes_Item $item) {
        $exclusions=array('type','src','post_id','id','is_template','localization');
        
        $html=      '<table class="'.$this->getCssClass('advanced-options').'">
                        <tbody>';
        
        foreach($item->getData() as $k=>$v) {
            if(!in_array($k,$exclusions)) {
                // Special rules for deps attribute
                if($k=='deps' && is_array($v))
                    $v=implode(',',$v);
                
                $html.='    <tr class="'.$this->getCssClass('advanced-option').'">
                                <td>
                                    <span>'.ucfirst(str_replace('_',' ',$k)).'</span>
                                </td>
                                <td>
                                    <input data-name="'.$k.'" type="text" name="'.((!$item->getIsTemplate())?$this->getFieldName($item,$k):'').'" value="'.esc_attr($v).'" />
                                </td>
                            </tr>';
            }
            
            // Special rendering for localization
            if($k=='localization') {
                $html.='    <tr class="'.$this->getCssClass('advanced-option').'">
                                <td>
                                    <span>'.ucfirst(str_replace('_',' ',$k)).'</span>
                                </td>
                                <td>
                                    <textarea data-name="'.$k.'" rows="8" class="block-field" name="'.((!$item->getIsTemplate())?$this->getFieldName($item,$k):'').'">'.stripslashes(htmlentities($v)).'</textarea>
                                </td>
                            </tr>';
            }
        }
        
        $html.=     '   </tbody>
                    </table';
        
        return $html;
    }
    
    /**
     * Prefix a class name with the plugin base CSS class name.
     * 
     * @param string $additional
     * @return string
     */
    public function getCssClass($additional='') {
        return "{$this->_baseCssClass} {$additional}";
    }

    /**
     * Generate an input field name for the item.
     * 
     * @param WP_Plugins_Postheadincludes_Item $item
     * @param string $name
     * @return string
     */
    public function getFieldName(WP_Plugins_Postheadincludes_Item $item,$name='') {
        return "{$this->_formOptionGroup}[{$item->getId()}_{$name}]";
    }
    
    /**
     * Your support is appreciated!
     * 
     * @return string
     */
    public function getDonationBoxHtml() {
        return  '<script>
                    function postheadincludes_donate() {
                        var form=document.createElement("form");
                        form.action="https://www.paypal.com/cgi-bin/webscr";
                        form.method="post";
                        form.target="_blank";
                        form.innerHTML=\'<input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="JCLTDKWT8KS2A"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">\';
                        document.body.appendChild(form);
                        form.submit();
                    }
                </script>
                <div class="'.$this->getCssClass('donationbox').'" style="float:right;">
                    <h4 style="margin:0 0 3px 0;">Support this Plugin for $1.00</h4>
                    <div align="center">
                        <a href="javascript:;" onclick="postheadincludes_donate();" title="PayPal - The safer, easier way to pay online!">
                            <img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
                        </a>
                    </div>
                </div>';
    }
    
    /**
     * Get the items in the collection for this post.
     * 
     * @return array
     */
    public function getItems() {
        return $this->getPlugin()->getItems(get_the_ID());
    }
    
    /**
     * Get the plugin.
     * 
     * @return \WP_Plugins_Postheadincludes
     */
    public function getPlugin() {
        return $this->_plugin;
    }
    
    /**
     * Prepare and return the POST data for the plugin.
     * 
     * @return array
     */
    public function getPost() {
        if(!isset($_POST['postheadincludes']) || empty($_POST['postheadincludes']))
            return array();
        
        $_post=$_POST['postheadincludes'];
        $post=array();
        foreach($_post as $field=>$value) {
            $setId=$this->_getPostSetId($field);
            $type=$this->_getPostSetType($field);
            
            // Require at least a type and source
            if( ($type=='type' && !$value) || 
                ($type=='src' && !$value) ) {
                wp_die(__('Please specify at least a type and source for your head includes.','postheadincludes'));
            }
            
            if(!isset($post[$setId]))
                $post[$setId]=array();
            
            // Special processing for deps
            if($type=='deps')
                $value=explode(',',str_replace(' ','',$value));
            
            $post[$setId][$type]=$value;
        }
        
        return $post;
    }
    
    /**
     * Extract the generated item ID from the field name.
     * This value is not really important (for now), because the entire 
     * collection of items for the post is rebuilt on save.
     * 
     * @param string $field
     * @return string
     */
    protected function _getPostSetId($field) {
        preg_match('/^'.WP_Plugins_Postheadincludes_Item::$_itemIdPrefix.'([^_]+)/',$field,$matches);
        if(count($matches)<2)
            return false;
        
        return $matches[1];
    }
    
    /**
     * Extract the item type from the field name.
     * 
     * @param string $field
     * @return string
     */
    protected function _getPostSetType($field) {
        preg_match('/^'.WP_Plugins_Postheadincludes_Item::$_itemIdPrefix.'[^_]+_(.+)$/',$field,$matches);
        if(count($matches)<2)
            return false;
        
        return $matches[1];
    }
    
    /**
     * Render the meta-box.
     */
    public function render() {
        wp_nonce_field(plugin_basename(__FILE__),'postheadincludes_nonce');
        echo $this->getDonationBoxHtml();
        include_once 'form.phtml';
    }
    
    /**
     * Save the plugin data for this post.
     * 
     * @param integer $post_id
     * @return \WP_Plugins_Postheadincludes_Metabox
     */
    public function save($post_id) {
        if('page'==$_POST['post_type']) {
            if(!current_user_can( 'edit_page',$post_id))
                return false;
        }
        else {
            if(!current_user_can('edit_post',$post_id))
                return false;
        }
        
        if(!isset($_POST['postheadincludes_nonce'] ) || !wp_verify_nonce($_POST['postheadincludes_nonce'],plugin_basename(__FILE__)))
            return false;
        
        if(count($data=$this->getPost())) {
            $this->getPlugin()
                    ->addData($data,$post_id)
                    ->save();
        }
        else
            $this->getPlugin()->removeData($post_id)->save();
        
        return $this;
    }
    
    /**
     * Set the plugin.
     * 
     * @param WP_Plugins_Postheadincludes $plugin
     * @return \WP_Plugins_Postheadincludes_Metabox
     */
    public function setPlugin(WP_Plugins_Postheadincludes $plugin) {
        $this->_plugin=$plugin;
        
        return $this;
    }
    
}