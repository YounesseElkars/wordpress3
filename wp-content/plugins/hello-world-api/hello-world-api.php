<?php
/**
 * Plugin Name: Hello World REST API
 * Description: Adds a REST API endpoint that returns "Hello, World!".
 * Version: 1.0
 * Author: Your Name
 */

// Register the REST API endpoint when WordPress initializes
add_action('rest_api_init', function () {
    register_rest_route('hello-world/v1', '/message/', [
        'methods' => 'GET',
        'callback' => 'hello_world_api_callback',
        'permission_callback' => '__return_true' // This makes the endpoint publically accessible without authentication
    ]);
});

// Callback function for the REST API endpoint
function hello_world_api_callback() {
    return new WP_REST_Response(['message' => 'Hello, World!'], 200);
}