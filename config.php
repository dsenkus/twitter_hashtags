<?php
class TwitterConfig{
  public static $config = array(
    // Twtitter API call limit per run
    // you can calculate needed call limit (or set it to 150):
    // call_limit = max_page + max_page * rpp 
    'call_limit'  => 16,
    // Results per page (max 100)
    'rpp'         => 100,
    // Max results page
    'max_page'    => 1,
    // use max_page=2 and rpp=73, if your call limit is 150

    // Twitter credentials (optional),
    // none of the calls in this script
    // requires credentials, but might be usefull in future
    'twitter'     => array(
      'user'      => '',
      'pass'      => '',
      'auth'      => false, // use credentials true/false
    ),

    // MySQL Setup
    'mysql'       => array (
      'host'      => 'localhost',
      'user'      => 'root',
      'pass'      => '',
      'db'        => 'hashtags',
      'table'     => 'tweets',
    ),

    // Usernames to exclude
    'exclude'     => array(
    ),

    // Illicit words list
    'illicit'     => array(
       'is', 'donut',
    ),

    // Downloaded images directory with trailing slash
    'img_dir'     => 
      '/home/dvs/Work/twitter_hashtags/images/',
    // Image size, possible values: S(42px), M(73px), L(original)
    // Original images doesn't always exist, so it safest to use S or M
    'img_size'    => 'M', 
    // Fallback url when Twitter profile image does not exist
    'img_fallback'=> 'http://www.hoiantourist.com/img/noimg.gif',

    // Display debug messages
    'debug'       => true,
  );
}
