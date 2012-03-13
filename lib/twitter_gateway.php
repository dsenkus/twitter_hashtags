<?php
class TwitterGateway {
  public static $http_status;
  public static $last_api_call;

	/**
	 * API call to search for tweets
	 * @param string $string Search text
	 * @param int $rpp Results Per Page
	 * @param int $page Page
	 * @param int $since_id Tweet id, used to fetch latest tweets
	 * @param string $until Date, used to fetch old tweets, format (YYYY-MM-DD)
	 * @return string
	 */
  public static function search($string, $rpp = 100, $page = 1, $since_id = 0, $until=""){
    return self::apiCall('search', 'get', 'json',
      array("q" => $string, "rpp" => $rpp, "page" => $page,
      "since_id" => $since_id, "until" => $until), "search.");
  }

	/**
	 * API call to get user profile
	 * @param string $user_id Twitter user id
	 * @return string
	 */
  public static function profile($screen_name){
    return self::apiCall("1/users/show", 'get', 'json',
      array('screen_name' => $screen_name), "api.");
  }

	/**
	 * Executes an API call
	 * @param string $twitter_method The Twitter method to call
   * @param string $http_method The HTTP method to use
   * @param string $format Return format
   * @param array $options Options to pass to the Twitter method
	 * @param string $api_prefix Needed for some api calls
	 * @return string
	 */
  protected static function apiCall($twitter_method, $http_method, $format, $options,
    $api_prefix = "")
  {
    $curl_handle = curl_init();
    $api_url = sprintf('http://%stwitter.com/%s.%s', $api_prefix,
      $twitter_method, $format);

    // get method
    if (($http_method == 'get') && (count($options) > 0)) {
      $api_url .= '?' . http_build_query($options);
    }

    Utils::log("API: $api_url");

    curl_setopt($curl_handle, CURLOPT_URL, $api_url);

    // set credentials if needed
    if (TwitterConfig::$config['twitter']['auth']) {
      $credentials = sprintf("%s:%s", TwitterConfig::$config['twitter']['user'], 
        TwitterConfig::$config['twitter']['pass']);
      curl_setopt($curl_handle, CURLOPT_USERPWD, $credentials);
    }

    // post method
    if ($http_method == 'post') {
      curl_setopt($curl_handle, CURLOPT_POST, true);
      curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($options));
    }

    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('Expect:'));

    // execute request
    $twitter_data = curl_exec($curl_handle);

    // might be usefull some time
    self::$http_status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
    self::$last_api_call = $api_url;

    curl_close($curl_handle);
    return $twitter_data;
  }
}
