<?php
class RESTClient {

  private $root_url = "";
  private $curr_url = "";
  private $user_name = "";
  private $password = "";
  private $response = "";
  private $responseBody = "";
  public $responseCode = 0;
  private $responseCookies = "";
  private $req = null;
  public $debug = 0;
  public $method = 0;
  public $inputs = null;
  //    private $above_this = null;

  public function __construct($root_url = "", $user_name = "", $password = "") {
    $this->root_url = $this->curr_url = $root_url;
    $this->user_name = $user_name;
    $this->password = $password;
    //	$this->above_this = $above_this;
    if ($root_url != "") {
      $this->createRequest("GET");
      $this->sendRequest();
    }
    return true;
  }



  public function createRequest($url, $method, $arr = null, $allow_redirects = TRUE) {
    $this->curr_url = $url;
    $this->method = $method;
    $this->req = new HTTP_Request($url, array('allowRedirects' => $allow_redirects));
    $this->inputs = $arr;
    if ($this->debug >=10) {
      echo "CONN:$url with $method\n";
    }

    if ($this->user_name != "" && $this->password != "") {
      $this->req->setBasicAuth($this->user_name, $this->password);
    }

    switch($method) {
      case "GET":
        $this->req->setMethod(HTTP_REQUEST_METHOD_GET);
        break;
      case "POST":
        $this->req->setMethod(HTTP_REQUEST_METHOD_POST);
        if (isset($arr)) {
          $this->addPostData($arr);
        }
        break;
      case "PUT":
        $this->req->setMethod(HTTP_REQUEST_METHOD_PUT);
        if (isset($arr)) {
          $this->addPostData($arr);
        }
        // to-do
        break;
      case "DELETE":
        $this->req->setMethod(HTTP_REQUEST_METHOD_DELETE);
        // to-do
        break;
    }
  }

  private function addPostData($arr) {
    if ($arr != null) {
      foreach ($arr as $key => $value) {
        $this->req->addPostData($key, $value);
      }
    }
  }

  public function setBody($body) {
    $this->req->setBody($body);
  }

  public function addCookie($key, $val) {
    $this->req->addCookie($key, $val);
  }

  /**
   * @deprecated sessid key should be session name, not 'sessid'.
   */
  public function addSessCookie($sessid,$session_name) {
    $this->req->addCookie("sessid",$sessid);
//    $this->req->addCookie("session_name",$session_name);
    $this->req->addCookie($session_name,$sessid);
  }

  public function addHeader ($key,$val) {
    $this->req->addHeader($key,$val);
  }

  public function sendRequest() {
    $this->responseCode = 0;
    $this->responseBody = "";
    $this->responseCookies = "";

    if ($this->debug) {
      echo "SEND-CONN:".$this->curr_url." with ".$this->method."\n";
    }

    $this->response = $this->req->sendRequest();
    if (PEAR::isError($this->response)) {
      echo $this->response->getMessage();
      die();
    } else {
      $this->responseCode = $this->req->getResponseCode();
      $this->responseBody = $this->req->getResponseBody();
      $this->responseCookies = $this->req->getResponseCookies();
    }
  }

  public function getResponse() {
    return $this->responseBody;
  }

  public function getResponseCookies() {
    return $this->responseCookies;
  }

}
?>
