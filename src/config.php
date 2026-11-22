<?php

// Settings, read from the environment. Loaded by Composer's autoloader.

/**
 * Read a setting from the environment, falling back to a default.
 *
 * @param string $name The environment variable name.
 * @param string $default The value to use when the variable is unset or empty.
 * @return string The setting.
 */
function env_string(string $name, string $default): string
{
    $value = getenv($name);
    return ($value === false || $value === '') ? $default : $value;
}

/**
 * Like env_string(), but for positive integers; anything else yields the default.
 *
 * @param string $name The environment variable name.
 * @param int $default The value to use when the variable is unset or not a positive integer.
 * @return int The setting.
 */
function env_int(string $name, int $default): int
{
    $value = filter_var(getenv($name), FILTER_VALIDATE_INT);
    return ($value === false || $value < 1) ? $default : $value;
}

// Settings. Override any of them with the environment variable named in the comment.
define('SITE_NAME', env_string('TTT_SITE_NAME', 'My blog'));
define('SITE_DESC', env_string('TTT_SITE_DESC', 'Powered by t.t.t, a database-free blog engine.'));
define('ITEMS_DISPLAYED_PER_PAGE', env_int('TTT_ITEMS_PER_PAGE', 10));
define('PREVIEW_SIZE_IN_BYTES', env_int('TTT_PREVIEW_BYTES', 1000));  // Roughly how much of each post the index shows.
define('CONTENT_DIR', env_string('TTT_CONTENT_DIR', 'content'));      // Where the text files live, relative to the web root.
// Comments via giscus (GitHub Discussions). All four must be set to enable them; get them from https://giscus.app.
define('GISCUS_REPO', env_string('TTT_GISCUS_REPO', ''));                  // owner/name of the repository holding the discussions
define('GISCUS_REPO_ID', env_string('TTT_GISCUS_REPO_ID', ''));
define('GISCUS_CATEGORY', env_string('TTT_GISCUS_CATEGORY', ''));          // discussion category name, e.g. Announcements
define('GISCUS_CATEGORY_ID', env_string('TTT_GISCUS_CATEGORY_ID', ''));
define('ANALYTICS_DOMAIN', env_string('TTT_ANALYTICS_DOMAIN', ''));   // Empty disables analytics.
define('ANALYTICS_SCRIPT_URL', env_string('TTT_ANALYTICS_SCRIPT_URL', 'https://plausible.io/js/script.js'));
define('SHOW_DRAFTS', env_string('TTT_SHOW_DRAFTS', '') !== '');         // Set (to anything) to preview posts marked `draft: true`.
define('EXT_ALLOWED', ['txt', 'md']);

// Where the posts live: relative to public/, and absolute.
define('CONTENT_REL_DIR', trim(constant('CONTENT_DIR'), '/'));
define('CONTENT_ABS_DIR', dirname(__DIR__).'/public/'.CONTENT_REL_DIR);
