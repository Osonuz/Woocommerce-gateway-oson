<?php

/* Oson payment connection class */
/* author: https://ktscript.ru   */

class ICExchanger {

  public $url;
  private $token = null;
  private $merchant_id = null;
  public $errmsg = '';
  public $errno = 0;
  
  public function __construct($args = null)
  {
    if ( !empty($args) ) {
      $this->url          = isset($args['serverUrl']) ? $args['serverUrl'] : null;
      $this->merchant_id  = isset($args['merchant_id']) ? $args['merchant_id'] : null;
      $this->token        = isset($args['token']) ? $args['token'] : null;
    }
  }
  
  public function query ($method, $params_post = null)
  {
    $ch  = curl_init();

    if (is_array($params_post)) {       
    	curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode(array_merge($params_post, ['merchant_id' => $this->merchant_id])));
    } 

    error_log($this->url . $method );
    error_log(json_encode($params_post));

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'token: '.$this->token
    ];

    curl_setopt($ch, CURLOPT_URL,  $this->url . $method );
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    //curl_setopt($ch, CURLOPT_NOSIGNAL,true);
    //curl_setopt($ch, CURLOPT_FAILONERROR, true);

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $errno = curl_errno($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    $result = json_decode($response);
    if ($errno > 0 || $result->error_code > 0 ) {
      $this->errno = $errno;
      error_log('1:'.$errno .' :: '.$response.' :: '.serialize( $info ));
      //throw new Exception('1:'.$errno .' :: '.$response.' :: '.serialize( $info ));
    } 

    return $result;
  }
  
  private function parse_array($var) {
    if(is_string($var)){
      $r = json_decode($var);
      if (is_array($r)){
        return $r;
      }
      if (is_object($r)){
        return (array)$r;
      }
      $var = preg_match("/\[.+\]/", $var, $m) ? $m[0] : null;
      return $var === null ? array() : json_decode($var);
    }

    return is_array($var) ? $var : (is_object($var) ? (array)$var : array());
  }
}