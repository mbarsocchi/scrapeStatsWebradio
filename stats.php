<?php
$fileCompletePath = dirname(__FILE__).'/config.json';
if (file_exists($fileCompletePath)) {
    $json = file_get_contents($fileCompletePath);
    $config = json_decode($json, true);
}else {
    die("no config.json file found in the root of this project, please provide ones writtens like this:"
            . "{
  \"dbConfig\": {
    \"servername\": \"<DB-HOST>\",
    \"username\": \"<DB-USERNAME>\",
    \"password\": \"<DB-PASSWORD>\",
    \"dbname\": \"<DB-TABLE-FOR-LISTNER>\"
  },
  \"radios\": {
    \"<RADIO-NUMBER-1>\": {
      \"url\": \"<WEBRADIO-SERVER-URL1>\",
      \"protocol\": \"shout\"
    },
    \"<RADIO-NUMBER-2>\": {
      \"url\": \"<WEBRADIO-SERVER-URL2>\",
      \"protocol\": \"ice\",
      \"position\": 9
    },
    \"<RADIO-NUMBER-3>\": {
      \"url\": \"<WEBRADIO-SERVER-URL3>\",
      \"protocol\": \"ice\",
      \"position\": 2
    }
  }
}
protocol can be: shout, shout2, ice
for protocol ice, position is the ordinal number of the streaming, for multiple streaming mount point");
}

function getDom($url) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.224 Safari/534.10');
    $html = curl_exec($curl);
    curl_close($curl);

    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $dom->preserveWhiteSpace = false;
    return $dom;
}

function getshoutcastlistner($dom) {
    $xpath = new DOMXPath($dom);
    $elements = $xpath->query("/html/body/table");
    if (isset($elements[2]) && isset($elements[2]->nodeValue)) {
        preg_match('/ with (.+?) of .+? listeners/', $elements[2]->nodeValue, $m);
        $res = $m[1];
    } else {
        $res = 0;
    }
    return $res;
}

function getshoutcast2listner($dom) {
    preg_match('/ with (.+?) of .+? listeners/', $dom->textContent, $m);
    return @$m[1];
}

function geticecastlistner($dom, $position) {
    $xpath = new DOMXPath($dom);
    $elements = $xpath->query("//td[@class='streamstats']");
    if (isset($elements[$position])) {
        $res = @$elements[$position]->nodeValue;
    } else {
        $res = 0;
    }
    return $res;
}

function insertListner($radioListner, $dbConfig) {

    $conn = new mysqli($dbConfig["servername"], $dbConfig["username"], $dbConfig["password"], $dbConfig["dbname"]);
    $date = date("Y-m-d H:i:s");
    $sql = "INSERT INTO `other_radio` (`date`, `listner`, `name`) VALUES ";
    foreach ($radioListner as $radioName => $listner) {
        if ($listner != null && $listner != "" && is_numeric($listner)) {
            $sql .= "('$date', $listner,'$radioName'),";
        }
    }
    $sql = substr($sql, 0, -1);
    if ($conn->query($sql) !== TRUE) {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

foreach ($config["radios"] as $radioName => $data) {
    $dom = getDom($data['url']);
    $listner = 0;
    if (is_object($dom) && $dom->textContent != "") {
        switch ($data['protocol']) {
            case "shout":
                $listner = getshoutcastlistner($dom);
                break;
            case "shout2":
                $listner = getshoutcast2listner($dom);
                break;
            case "ice":
                $listner = geticecastlistner($dom, $data['position']);
                break;
            default:
                die("protocol " . $data['protocol'] . " not supported");
                break;
        }
    }
    $res[$radioName] = $listner;
}
//print_r($res);die();
insertListner($res, $config["dbConfig"]);
