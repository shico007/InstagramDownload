<?php
class InstagramDownload {
  public $input_url;
  public $id;
  
  public $type = 'image';
  public $download_url;
  public $meta_values = array();
  
  public  $error_code = 0;
  
  const INSTAGRAM_DOMAIN = 'instagram.com';
  
  
  function __construct($url = '') {
    if (!empty($url)) {
      $this->setUrl($url);
    }
  }
  
  public function setUrl($url) {
    $id = $this->validateUrl($url);
    if ($id && !is_numeric($id)) {
      $this->id = $id; 
      $this->input_url = $url;
    }
    else {
      $this->id = FALSE;
    }
  }
  
  public function type() {
    return $this->type;
  }
  
  public function downloadUrl($force_dl = TRUE) {
    if ($this->getError()) {
      return FALSE;
    }
    $status = $this->process($this->input_url);
    if ($status) {
      if ($force_dl) {
        return $this->download_url . '?dl=1';
      }
      return $this->download_url;
    }
    return FALSE;
  }

  protected function process(){
    $this->fetch($this->input_url);
    if (!is_array($this->meta_values)) {
      $this->meta_values = array();
      return FALSE;
    }
    if (!empty($this->meta_values['og:video'])) {
      $this->type = 'video';
      $this->download_url = $this->meta_values['og:video'];
    }
    elseif (!empty($this->meta_values['og:image'])) {
      $this->type = 'image';
      $this->download_url = $this->meta_values['og:image'];
    }
    else {
      return FALSE;
    }
    return $this->download_url;
  }

  public function validateUrl($url = NULL) {
    if (is_null($url) && isset($this->input_url)) {
      $url = $this->input_url;
    }
    $url = parse_url($url);
    if (empty($url)) {
      $this->error_code = -1;
      return FALSE;
    }
    
    $url['host'] = strtolower($url['host']);
    
    if ($url['host'] != self::INSTAGRAM_DOMAIN && $url['host'] != 'www.' . self::INSTAGRAM_DOMAIN) {

      $this->error_code = -2;
      return FALSE;
    }
    if (empty($url['path'])) {
      $this->error_code = -3;
      return FALSE;
    }
    $args = explode('/', $url['path']);
    if (!empty($args[1]) && $args[1] == 'p' && isset($args[2], $args[2][4]) && !isset($args[2][255])) {
      $this->error_code = 0;
      return $args[2];
    }
    $this->error_code = -4;
    return FALSE;
  }

  protected function fetch($URI) {
    $curl = curl_init($URI);

    curl_setopt($curl, CURLOPT_FAILONERROR, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

    $response = curl_exec($curl);

    curl_close($curl);

    if(!empty($response)) {
      return $this->_parse($response);
    }
    else {
      return false;
    }
  }

  protected function _parse($HTML) {
    $rawTags = array();

    preg_match_all("|<meta[^>]+=\"([^\"]*)\"[^>]" . "+content=\"([^\"]*)\"[^>]+>|i", $HTML, $rawTags, PREG_PATTERN_ORDER);

    if(!empty($rawTags)) {
      $multiValueTags = array_unique(array_diff_assoc($rawTags[1], array_unique($rawTags[1])));

      for($i=0; $i < sizeof($rawTags[1]); $i++) {
        $hasMultiValues = false;
        $tag = $rawTags[1][$i];

        foreach($multiValueTags as $mTag) {
          if($tag == $mTag)
            $hasMultiValues = true;
        }

        if($hasMultiValues) {
          $this->meta_values[$tag][] = $rawTags[2][$i];
        }
        else {
          $this->meta_values[$tag] = $rawTags[2][$i];
        }
      }
    }

    if (empty($page->meta_values)) { return false; }

    return $page;
  }

  public function getError() {
    if ($this->error_code !== TRUE && $this->error_code !== 0) {
      return self::error($this->error_code);
    }
    return NULL;
  }

  static function error($id) {
    $errors = array(
      -1 => 'Invalid URL',
      -2 => 'Entered URL is not an ' . self::INSTAGRAM_DOMAIN . ' URL.',
      -3 => 'No image or video found in this URL',
      -4 => 'No image or video found in this URL',
    );

    if (isset($errors[$id])) {
      return $errors[$id];
    }
    return 'Unknown error';
  }
}