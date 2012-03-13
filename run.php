<?php
require_once 'config.php';
require_once 'lib/utils.php';
require_once 'lib/twitter_gateway.php';
require_once 'lib/twitter_db_wrapper.php';
require_once 'lib/twitter_entry.php';
require_once 'lib/twitter_worker.php';

/**
 * Script algorithm description:
 * 
 * 1) On first run you should use $worker->getLatest($tag)
 *    It will download latest tweets and start inserting 
 *    them into database from oldest one.
 * 2) Any subsequent script run will check for most recent
 *    tweet_id and fetch only newer tweets.    
 * 3) Use $worker->fetchOld($tag) If you would like to 
 *    fetch older tweets.
 */

// tag, should include # symbol
$tag = "#donuts";
$worker = new TwitterWorker();

// fetch new tweets
$worker->fetchLatest($tag); 

// fetch older tweets
//$worker->fetchOld($tag); 
