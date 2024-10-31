<?php

/**
 * Source class for item types.
 * 
 * @package Postheadincludes
 * @author Rick Buczynski <richard.buczynski@gmail.com>
 * @version 0.2.1
 */

class WP_Plugins_Postheadincludes_Sources {
    
    public function getOptions() {
        return array(
            array(
                'value' => '',
                'label' => __('Select One','postheadincludes')
            ),
            array(
                'value' => 'script',
                'label' => __('JavaScript','postheadincludes')
            ),
            array(
                'value' => 'style',
                'label' => __('Stylesheet','postheadincludes')
            )
            /**
             * You can add additional sources here, where value can be either
             * the function name extending from `wp_enqueue_` or else a custom
             * function name to handle the item.
             */
        );
    }
    
}