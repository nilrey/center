<?php

/*
POST /public/api/idm/set/ HTTP/1.1
Authorization: Explicit kraken@domain
Content-Type: application/json
Host: http://1adr-book02.dev.aorti.tech:8840
Content-Length: 24
 
{
  "name": "TestSet"
}
*/
    $credentials = "citis@dev.aorti.tech:DFfwk32a";
    

  /*         
    $url = "http://1adr-book02.dev.aorti.tech:8840/public/api/idm/set/";
    $headers = array(
"GET /public/api/idm/set/ HTTP/1.1",
"Authorization: Explicit kraken@dev.aorti.tech",
"Content-Type: application/json",
"Host: 1adr-book02.dev.aorti.tech:8840"
        // "Authorization: Basic " . base64_encode($credentials)
    );
  
$ch = curl_init('http://1adr-book02.dev.aorti.tech:8840');

// curl_setopt($ch, CURLOPT_USERPWD, 'citis@dev.aorti.tech:');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($ch, CURLOPT_HEADER, $headers);

$html = curl_exec($ch);

curl_close($ch);

echo $html;

*/
    $url = 'http://1adr-book02.dev.aorti.tech:8840/public/api/idm/set/' 
    /*.http_build_query([
            'authkey' => '2011AQTvWQjrcB56d9b03d',
            'type' => '4'
        ])*/
        ;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http == 200) {
        $json = @json_decode($response, TRUE);
        return $json;
    } else {
        echo 'There was a problem ...';
    }


?>