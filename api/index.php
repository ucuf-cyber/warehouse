<?php

/**
 * Vercel serverless function entry point for Laravel.
 *
 * public/index.php is renamed to public/app-entry.php during the Vercel
 * build (see vercel.json's buildCommand) — Vercel's static file server
 * takes precedence over rewrites for any literal file that exists in the
 * output directory, so a literal "index.php" at the public root would
 * otherwise be served raw (and downloaded) for "/" instead of executing
 * through this function. See https://vercel.com/docs/project-configuration/vercel-json#rewrites
 */
// This file physically lives at api/index.php only because Vercel requires
// PHP functions to sit under an api/ directory — the app itself is mounted
// at the site root, not at "/api". Left alone, Symfony's Request builds its
// base path from SCRIPT_NAME ("/api/index.php") and strips a leading "/api"
// from every incoming URL that happens to share it, which silently 404s the
// entire routes/api.php route table (Laravel prefixes those with "api/").
// Normalizing SCRIPT_NAME here stops that stripping.
$_SERVER['SCRIPT_NAME'] = '/index.php';
if (isset($_SERVER['PHP_SELF'])) {
    $_SERVER['PHP_SELF'] = '/index.php';
}

require __DIR__.'/../public/app-entry.php';
