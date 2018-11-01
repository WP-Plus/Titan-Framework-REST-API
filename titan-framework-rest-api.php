<?php

/**
 * Plugin Name: Titan Framework REST API
 * Plugin URI: https://github.com/WP-Plus/Titan-Framework-REST-API
 * Description: [WIP] Simple REST API endpoints for Titan Framework.
 * Version: 0.1.0
 * Author: WP-Plus
 * Author URI: https://github.com/WP-Plus
 * License: MIT
 * License URI: https://github.com/WP-Plus/Titan-Framework-REST-API/blob/master/LICENSE
 */

namespace Wpp\TitanFrameworkRestApi;

define(__NAMESPACE__ . '\PLUGIN_BASE_PATH', dirname(__FILE__));

// Wait until plugins are loaded, so we can use some classes from Titan Framework.
add_action(
    'plugins_loaded',
    function() {
        require_once PLUGIN_BASE_PATH . '/includes/endpoints/wp-rest-titan-framework-controller.php';
        add_action('rest_api_init', function() {
            (new Endpoints\WP_REST_Titan_Framework_Controller())->register_routes();
        });
    },
    100
);

