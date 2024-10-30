<?php
/*
Plugin Name: JVH webbouw - Easy login
Description: This plugin is for JVH webbouw to securely login to this website.
Version: 1.3.1
Author: JVH webbouw | Lars Jansen
Author URI: https://www.jvhwebbouw.nl
License: GPL v3
Requires PHP: 7.4
Requires at least: 6.0
*/

use JVH\EasyLogin;

require_once __DIR__ . '/EasyLogin.php';

add_filter( 'body_class', static function ( array $classes ) {
	$classes[] = 'jvh-api2';

	return $classes;
} );

add_action( 'init', static function () {
	new EasyLogin();
} );
