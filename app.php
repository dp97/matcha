<?php

// Require the Composer autoloader into your PHP script.
require 'vendor/autoload.php';

// Slims Settings
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = 'localhost';
$config['db']['user']   = 'root';
$config['db']['pass']   = '';
$config['db']['dbname'] = 'matcha';

// Creating the Application.
$app = new \Slim\App(['settings' => $config]);


// Set up middlewares
require 'src/middlewares.php';


// Set up dependencies
require 'src/dependencies.php';


// Route for proccessing register form.
require 'src/routes.php';


// Start the application.
$app->run();

?>