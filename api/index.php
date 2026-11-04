<?php

// Vercel serverless entry point for Laravel
// This file routes all requests through Laravel's public/index.php

// Set the working directory to the project root
$root = dirname(__DIR__);
chdir($root);

// Override the document root so Laravel can find public assets
$_SERVER['DOCUMENT_ROOT'] = $root . '/public';

// Laravel expects requests to come through public/index.php
require $root . '/public/index.php';
