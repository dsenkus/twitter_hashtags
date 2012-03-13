<?php
class TwitterEntry {
  var $tweetObj; 
  var $authorName;
  var $valid;
  
  function __construct($resultObj){
    $this->tweetObj = $resultObj; 
    $this->isValid();
  }

	/**
	 * Save Tweet to database
	 */
  public function save(){
    if ($this->getAuthorInfo()){
      if(!$this->userExists()){
        $this->downloadProfileImages();
      } else {
        Utils::log("ProfileImage: Skipped (found in DB)");
      }
      $sql = "INSERT INTO %s (tweet_id, username, name, caption, valid, img_url, created_at) values (?,?,?,?,?,?,?)";
      $params = array('ssssiss',$this->tweetObj->id_str, $this->tweetObj->from_user, $this->authorName, 
        $this->tweetObj->text, $this->valid, $this->tweetObj->profile_image_url,
        date("Y-m-d H:i:s", strtotime($this->tweetObj->created_at)));

      TwitterDBWrapper::query($sql, $params);
    } 
  }

	/**
	 * Is username in excluded users list
	 * @return bool
	 */
  public function isExcluded(){
    return in_array($this->tweetObj->from_user, TwitterConfig::$config['exclude']);
  }

	/**
	 * Is tweet valid
	 * @return int 
	 */
  public function isValid(){
    foreach(TwitterConfig::$config['illicit'] as $word){
      if (preg_match("/\b$word\b/i", $this->tweetObj->text)) { 
        return $this->valid = 0;
      } 
    }
    return $this->valid = 1; 
  }

	/**
	 * Does tweet id exists in database
	 * @return bool 
	 */
  public function exists(){
    $sql = "SELECT tweet_id FROM %s WHERE tweet_id = ?";
    $params = array('s',$this->tweetObj->id_str);

    $res = TwitterDBWrapper::query($sql, $params);
    if($res->fetch()){
      return true;
    } else {
      return false;
    }
  }

	/**
	 * Does username exist in database
	 * @return bool
	 */
  private function userExists(){
    if(!$this->valid){
      $sql = "SELECT tweet_id FROM %s WHERE username = ?";
    } else {
      $sql = "SELECT tweet_id FROM %s WHERE username = ? AND valid = 1";
    }
    $params = array('s',$this->tweetObj->from_user);

    $res = TwitterDBWrapper::query($sql, $params);
    if($res->fetch()){
      return true;
    } else {
      return false;
    }
  }

	/**
	 * Fetch tweet author info from Twitter
	 * @return bool
	 */
  private function getAuthorInfo(){
    $res = json_decode(
      TwitterGateway::profile($this->tweetObj->from_user));
    if(isset($res->name)){
      $this->authorName = $res->name;
    } else {
      $this->authorName = '---';
      Utils::log("Could not get author info, setting name as '---'");
    }
    return true;
  } 
  
	/**
	 * Download author profile image
	 * @param bool $force force downloadign
	 */
  private function downloadProfileImages($force = false){
    $urls = $this->getImageUrls();
    $url = $urls[TwitterConfig::$config['img_size']];

    $ch = curl_init($url);

    // set file and dir names
    $dir = TwitterConfig::$config['img_dir'] 
      .  date("Y-m-d", time()) . '/';
    $dir .= $this->valid? 'valid_photos/' : 'invalid_photos/';
    $ext = Utils::findexts($url);
    $file = $dir . $this->tweetObj->from_user . ".$ext";
    
    // check for existing file, or force download
    if($force || !file_exists($file)){
      if(!is_dir($dir)){
        if (!mkdir($dir,0777,1)){
          Utils::log("Could not mkdir: $dir");
          exit();
        }
      }

      $fp = fopen($file, 'wb');
      curl_setopt($ch, CURLOPT_FILE, $fp);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_exec($ch);
      curl_close($ch);
      fclose($fp);

      Utils::log("ProfileImage: Downloaded");
    } else {
      Utils::log("ProfileImage: Skipped (file exists)");
    }
  }

  /**
   * Returns a list of available profile images urls
   * @return array
   */
  protected function getImageUrls(){
    $url = $this->tweetObj->profile_image_url;
    $r_normal = '/(_normal)(\.?[A-Z]*)$/i';
    $r_small = '/(_small)(\.?[A-Z]*)$/i';

    if(Utils::validUrl($url)){
      $results = array(
        'S' => $url,
      );

      if(preg_match($r_normal,$url)){
        $results['M'] = preg_replace($r_normal,'_bigger${2}',$url);
        $results['L'] = preg_replace($r_normal,'${2}',$url);
      } else if (preg_match($r_small,$url)){
        $results['M'] = $url;
        $results['L'] = preg_replace($r_small,'${2}',$url);
      } else {
        $results['M'] = $url;
        $results['L'] = $url;
      }

      if(!Utils::validUrl($results['M'])){
        $results['M'] = $url;
      }

      if(!Utils::validUrl($results['L'])){
        $results['L'] = $results['M'];
      }
      return $results;
    } else {
      // return fallback img
      return array(
        'S' => TwitterConfig::$config['img_fallback'],
        'M' => TwitterConfig::$config['img_fallback'],
        'L' => TwitterConfig::$config['img_fallback'],
      );        
    }
  }
}
