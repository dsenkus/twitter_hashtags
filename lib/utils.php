<?php
class Utils {
	/**
	 * Get extension of filename
	 * @param string $filename filename string
	 * @return string
	 */
  public static function findexts ($filename) { 
    $filename = strtolower($filename) ; 
    $exts = split("[/\\.]", $filename) ; 
    $n = count($exts)-1; 
    $exts = $exts[$n]; 

    // some of the files might not have extensions
    $exts = strlen($exts) > 4 ? 'jpg' : $exts;

    return $exts; 
  }

	/**
	 * Check if url valid (http code is not 404)
	 * @param string $url Url
	 * @return bool
	 */
  public static function validUrl($url){
    $handle = curl_init($url);
    curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

    /* Get the HTML or whatever is linked in $url. */
    $response = curl_exec($handle);

    /* Check for 404 (file not found). */
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    if($httpCode != 200) {
      return false;
    }
    curl_close($handle);

    return true;
  }

	/**
	 * Log text
	 * @param string $string text to log
	 * @return string
	 */
  public static function log($string){
    if (TwitterConfig::$config['debug']) {
      echo $string . "\n";
    }
  }
}
