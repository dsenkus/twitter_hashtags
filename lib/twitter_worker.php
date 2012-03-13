<?php
class TwitterWorker {
  function __construct(){
    // number of executed API calls
    $this->call_count = 0;
  }

	/**
	 * Fetch older tweets
	 * @param string $string Search string
	 */
  public function fetchOld($string){
    $rpp = TwitterConfig::$config['rpp'];
    $page = 1;
    $since_id = 0;
    $until = $this->fetchOldestDate();
    $this->doFetch($string,$rpp,$page,$since_id,$until,false);
  }

	/**
	 * Fetch latest tweets
	 * @param string $string Search string
	 */
  public function fetchLatest($string){
    $rpp = TwitterConfig::$config['rpp'];
    $page = 1;
    $since_id = $this->getLatestId();
    $until = "";
    $this->doFetch($string,$rpp,$page,$since_id,$until,true);
  }

	/**
	 * Do actual fetching
	 * @param string $string Search string
	 * @param int $rpp Results Per Page
	 * @param int $page Start page
	 * @param int $since_id Latest tweet id
	 * @param string $until YYYY-MM-DD format date for older tweets
	 * @param bool $reverse Reverse search results, needed when fetching latest tweets
	 */
  protected function doFetch($string, $rpp, $page, $since_id, $until, $reverse){
    $all_tweets = array();

    // fetch results from all pages
    do {
      $results = json_decode(
        TwitterGateway::search($string, $rpp, $page, $since_id, $until));
      $this->call_count++;

      if (isset($results->results)){
        $all_tweets = array_merge($all_tweets, $results->results);
        $results_count = count($results->results);
        $page++;
      } else {
        $results_count = 0;
      }
    } while ($results_count >= $rpp && !$this->callLimitReached()
        && TwitterConfig::$config['max_page'] >= $page);

    // reverse array, so proccessing will start from oldest tweet
    if($reverse){
      $all_tweets = array_reverse($all_tweets);
    }

    // process all tweets
    foreach ($all_tweets as $tweetObj){
      if(!$this->callLimitReached()){
        $tweet = new TwitterEntry($tweetObj);
        if(!$tweet->isExcluded()){
          if(!$tweet->exists()){
            Utils::log("\nSaving {$tweetObj->id_str}");
            $tweet->save();

            $this->call_count++;
          } else {
            Utils::log("\nSkipping {$tweetObj->id_str} (exists)");
          }
        } else {
          Utils::log("\nSkipping {$tweetObj->id_str} (excluded)");
        }
      }
    }

  }

	/**
	 * Is call limit reached?
	 * @return bool
	 */
  protected function callLimitReached(){
    if ($this->call_count < TwitterConfig::$config['call_limit']){
      return false;
    }

    return true;
  }

	/**
	 * Id of the latest tweet in DB
	 * @return int
	 */
  protected function getLatestId(){
    $sql = "SELECT tweet_id FROM %s ORDER BY tweet_id DESC LIMIT 1";
    if($res = TwitterDBWrapper::fetch($sql)){
      $res = $res->fetch_array();
      return $res[0];
    }
    return 0;
  }

	/**
	 * Date of the oldest tweet in database
	 * @return string
	 */
  protected function fetchOldestDate(){
    $sql = "SELECT created_at FROM %s ORDER BY created_at ASC LIMIT 1";
    $date = "";

    if($res = TwitterDBWrapper::fetch($sql)){
      $res = $res->fetch_array();
      if(!empty($res[0])){
        $date = date("Y-m-d", strtotime($res[0]));
      }
    }

    return $date;
  }
}
