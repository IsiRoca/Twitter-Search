<?php
/**
* Twitter Search
*
* PHP version 5.0
*
* @author     Isi Roca
* @copyright  Copyright (C) 2016 Isi Roca
* @link       http://isiroca.com
* @since      File available since Release 1.0.0
* @license    https://opensource.org/licenses/MIT  The MIT License (MIT)
* @see        https://github.com/IsiRoca/Twitter-Search/issues
*
*/

/**
 * Twitter Search API.
 */
class Twitter
{
    const TWITTER_API_URL = 'https://api.twitter.com/1.1/';

    /** @var array */
    public $httpOptions = array(
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTPHEADER => array('Expect:'),
        CURLOPT_USERAGENT => 'Twitter for PHP',
    );

    /** @var Twitter_OAuthConsumer */
    private $consumer;

    /** @var Twitter_OAuthConsumer */
    private $token;

    /**
     * Creates object using consumer and access keys.
     * @param  string  consumer key
     * @param  string  app secret
     * @param  string  optional access token
     * @param  string  optinal access token secret
     * @throws TwitterException when CURL extension is not loaded
     */
    public function __construct($consumerKey, $consumerSecret, $oauthAccessToken = NULL, $oauthAccessTokenSecret = NULL)
    {
        if (!extension_loaded('curl')) {
            throw new TwitterException('PHP extension CURL is not loaded.');
        }

        $this->consumer = new Twitter_OAuthConsumer($consumerKey, $consumerSecret);
        $this->token = new Twitter_OAuthConsumer($oauthAccessToken, $oauthAccessTokenSecret);
    }

    /**
     * Returns tweets that match a specified query.
     * @param  string|array
     * @param  bool  return complete response?
     * @return stdClass  see https://dev.twitter.com/rest/reference/get/search/tweets
     */
    public function search($query, $full = FALSE)
    {
        $response = $this->request('search/tweets', 'GET', is_array($query) ? $query : array('q' => $query));
        return $full ? $response : $response->statuses;
    }

    /**
     * Processing HTTP request.
     * @param  string  URL or twitter command
     * @param  string  HTTP method GET or POST
     * @return stdClass|stdClass[]
     * @throws TwitterException
     */
    public function request($resource, $method, array $data = NULL, array $files = NULL)
    {
        if (!strpos($resource, '://')) {
            if (!strpos($resource, '.')) {
                $resource .= '.json';
            }
            $resource = self::TWITTER_API_URL . $resource;
        }

        $hasCURLFile = class_exists('CURLFile', FALSE) && defined('CURLOPT_SAFE_UPLOAD');

        $request = Twitter_OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $resource, $files ? array() : $data);
        $request->sign_request(new Twitter_OAuthSignatureMethod_HMAC_SHA1, $this->consumer, $this->token);

        $options = array(
            CURLOPT_HEADER => FALSE,
            CURLOPT_RETURNTRANSFER => TRUE,
        ) + ($method === 'POST' ? array(
            $hasCURLFile ? CURLOPT_SAFE_UPLOAD : -1 => TRUE,
            CURLOPT_POST => FALSE,
            CURLOPT_POSTFIELDS => $files ? $data : $request->to_postdata(),
            CURLOPT_URL => $files ? $request->to_url() : $request->get_normalized_http_url(),
        ) : array(
            CURLOPT_URL => $request->to_url(),
        )) + $this->httpOptions;

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new TwitterException('Server error: ' . curl_error($curl));
        }

        $response = defined('JSON_BIGINT_AS_STRING')
            ? @json_decode($result, FALSE, 128, JSON_BIGINT_AS_STRING)
            : @json_decode($result);

        if ($response === FALSE) {
            throw new TwitterException('Invalid server response');
        }

        $errno = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($errno >= 400) {
            throw new TwitterException(isset($payload->errors[0]->message)
                ? $payload->errors[0]->message
                : "Server error #$errno",
                $errno
            );
        }

        return $response;
    }

    /**
     * Twitter links, @usernames and #hashtags formated.
     * @param  stdClass  result
     * @return string
     */
    public static function format($result, $query)
    {
        $all = array();
        foreach ($result->entities->hashtags as $item) {
            $all[$item->indices[0]] = array("http://twitter.com/search?q=%23$item->text", "#$item->text", $item->indices[1]);
        }
        foreach ($result->entities->urls as $item) {
            if (!isset($item->expanded_url)) {
                $all[$item->indices[0]] = array($item->url, $item->url, $item->indices[1]);
            } else {
                $all[$item->indices[0]] = array($item->expanded_url, $item->display_url, $item->indices[1]);
            }
        }
        foreach ($result->entities->user_mentions as $item) {
            $all[$item->indices[0]] = array("http://twitter.com/$item->screen_name", "@$item->screen_name", $item->indices[1]);
        }
        if (isset($result->entities->media)) {
            foreach ($result->entities->media as $item) {
                $all[$item->indices[0]] = array($item->url, $item->display_url, $item->indices[1]);
            }
        }

        krsort($all);
        $s = $result->text;
        foreach ($all as $pos => $item) {
            $s = iconv_substr($s, 0, $pos, 'UTF-8')
                . '<a href="' . htmlspecialchars($item[0]) . '">' . htmlspecialchars($item[1]) . '</a>'
                . iconv_substr($s, $item[2], iconv_strlen($s, 'UTF-8'), 'UTF-8');
        }

        preg_match('/(?:\w+(?:\W+|$)){0,100}/',$query, $query_array);

        if(strlen($s) > 0 && strlen($query) > 0)
        {
            if ($query_array = TRUE) {
                $txt = str_ireplace($query, "<span style='background-color:#EEEE00'>".$query."</span>", $s);
            }else{
                $txt = str_ireplace($query, "<span style='background-color:#EEEE00'>".$query."</span>", $s);
            }
        }

        return $txt;
    }

}

/**
 * Exception generated by Twitter.
 */
class TwitterException extends Exception
{
}
