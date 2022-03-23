<?php
    date_default_timezone_set("Asia/Jakarta");
    //Data From Webhook
    $content = file_get_contents("php://input");
    $update = json_decode($content, true);
    $chat_id = $_ENV['CHAT_ID'];
    $apiToken = $_ENV['API_TOKEN'];  
    $admin_id = $_ENV['ADMIN_ID'];  
    $message = $update["message"]["text"];
    $message_chat_id = $update["message"]["chat"]["id"];
    $message_id = $update["message"]["message_id"];
    $id = $update["message"]["from"]["id"];
    $username = $update["message"]["from"]["username"];
    $firstname = $update["message"]["from"]["first_name"];

if ($message_chat_id == $chat_id) {
    if (strpos($message, "/init") === 0) {
          $json = file_get_contents("https://api.telegram.org/bot$apiToken/getMyCommands");
        $data = json_decode($json, true);
        
        $ok = $data['ok'];
        if ($ok == true and strtoupper($username) === strtoupper($admin_id)) {
            file_get_contents("https://api.telegram.org/bot$apiToken/deleteMessage?chat_id=$chat_id&message_id=$message_id");
            file_get_contents("https://api.telegram.org/bot$apiToken/setMyCommands?commands=[{%22command%22:%22/bin%22,%22description%22:%22/bin%20Card header%20 Query credit card information%22},{%22command%22:%22/rate%22,%22description%22:%22/rate%20currency1%20currency2%20quantity%20query currency exchange rate%22}]" );
        }
    }
    //Bin Lookup
    if (strpos($message, "/bin") === 0) {
        $bin = substr($message, 5);
          $json = file_get_contents("https://binsu-api.vercel.app/api/{$bin}");
        $data = json_decode($json, true);
        $bank = $data['data']['bank'];
        $country = $data['data']['country'];
        $brand = $data['data']['vendor'];
        $level = $data['data']['level'];
        $type = $data['data']['type'];
        $flag = $data['data']['countryInfo']['emoji'];
        $result = $data['result'];
    
        if ($result == true) {
            send_message($apiToken,$chat_id, $message_id, "***Card Head: $bin
Brand: $brand
Level: $level
Bank: $bank
Nation: $country $flag
Type: $type***
Inquirer @$username");
        } else {
            send_message($apiToken,$chat_id, $message_id, "***Card header parsing error*** Correct format: /bin 6-8 bit card header\nBy @Yoga_CIC");
        }
    }
    //-rate CNY TRY 100
    if (strpos($message, "/rate") === 0) {
        $apikey = "c1bf309f1ed58b0e54c8";
        $from_Currency = urlencode(strtoupper(substr($message, 6, 3)));
        $to_Currency = urlencode(strtoupper(substr($message, 10, 3)));
          $query =  "{$from_Currency}_{$to_Currency}";
        
          $json = file_get_contents("https://free.currconv.com/api/v7/convert?q={$query}&compact=ultra&apiKey={$apikey}");
          $obj = json_decode($json, true);
        
        if (empty(substr($message, 14)) == false) {
            $amount = floatval(substr($message, 14));
        } else {
            $amount = 100;
        }
        
        if (empty($obj) == false) {
            if (empty($obj["$query"]) == false) {
                $val = floatval($obj["$query"]);
                $total = round($val * $amount,2);
                send_message($apiToken,$chat_id, $message_id, "***$from_Currency : $to_Currency = $amount : $total ***
Inquirer @$username ");
            } else {
                send_message($apiToken,$chat_id,$message_id, "***Exchange rate parsing error*** Correct format: /rate currency1 currency2 quantity");
            }
        } else {
            send_message($apiToken,$chat_id,$message_id, "***Exchange rate parsing error*** Correct format: /rate currency1 currency2 quantity");
        }
    }
}

function send_message($apiToken,$chat_id,$message_id, $message){
    $text = urlencode($message);
    file_get_contents("https://api.telegram.org/bot$apiToken/deleteMessage?chat_id=$chat_id&message_id=$message_id");
    file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?chat_id=$chat_id&text=$text&parse_mode=Markdown");
}
?>
