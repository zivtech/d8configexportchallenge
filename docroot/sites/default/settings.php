<?php

ini_set('arg_separator.output', '&amp;');
ini_set('magic_quotes_runtime', 0);
ini_set('magic_quotes_sybase', 0);
ini_set('session.cache_expire', 200000);
ini_set('session.cache_limiter', 'none');
ini_set('session.cookie_lifetime', 0);
ini_set('session.gc_divisor', 100);
ini_set('session.gc_probability', 1);
ini_set('session.gc_maxlifetime', 200000);
ini_set('session.save_handler', 'user');
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);
ini_set('url_rewriter.tags', '');

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
$base_url = $protocol . 'configchallenge.vm';

$conf = array(
  'fetcher_environment' => 'local',
);
$databases = array(
  'default' => array(
    'default' => array(
      'database' => 'configchallenge',
      'username' => 'configchallenge',
      'password' => '8bBqVTa71l9IskWLp0cw',
      'host' => 'localhost',
      'port' => '',
      'driver' => 'mysql',
      'prefix' => '',
      'namespace' => 'Drupal\Core\Database\Driver\mysql',
    ),
  ),
);
$settings = array(
  'container_yamls' => array(
    '0' => '/usr/share/drush/commands/drush_fetcher/lib/Fetcher/Configurator/DrupalVersion/services.yml',
  ),
);$databases['default']['default'] = array (
  'database' => 'configchallenge',
  'username' => 'configchallenge',
  'password' => '8bBqVTa71l9IskWLp0cw',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
$settings['hash_salt'] = 'yTr0Unv6zH1DICtXJD6814rBLTYFGqaHvke9oAoICFRuC7_JdROdEoq9lAkoc_Z-Lug6PQGHHw';
$settings['install_profile'] = 'standard';
$config_directories['sync'] = 'sites/default/files/config_vXqWjx2o2hEEZWaCjoyjHN5PYFONqSh5MuXp0dO-D1UAiKTvW52zCbp4KJ5erxgyHSJ93iBclg/sync';
