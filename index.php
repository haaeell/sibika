<?php

// Fallback entry point for hosting where document root is project root.
// If mod_rewrite is enabled, .htaccess will route to public/index.php.
// This file ensures direct access to /index.php still boots the app.

require __DIR__ . '/public/index.php';
