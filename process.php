<?php
// @error_reporting(E_ALL);
// @ini_set('display_errors', true);
// @ini_set('html_errors', true);
// @ini_set('display_startup_errors', true);
// @ini_set('log_errors', false);
@error_reporting(0);

// echo '<pre>';
// var_dump($_POST);
// echo '</pre>';

if (isset($_POST['msg'])) {
    $apikey =   $_POST['apikey'];
    $url    =   $_POST['url'];
    $from   =   $_POST['from'];
    $msg    =   $_POST['msg'];
    $to =   $_POST['to'];
    $site_select =   $_POST['site_select'];

    //echo '<pre>';
    //echo "<b>POSTED FIELDS:</b> ";
    //print_r($_POST);die;
    function SEND_SMS($apikey, $url, $from, $msg, $to)
    {
        // New API
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url.'/sms/2/text/single',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '
            {
                "from":"'.$from.'",
                "to":['.$to.'],
                "text":'.$msg.'
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: App '.$apikey,
                'Content-Type: application/json',
                'Accept: application/json'
            ) ,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $decoded = json_decode($response);
		return $decoded;
    }

    function SEND_SMS_Clickatell($apikey, $url, $from, $msg, $to){
        $url = $url.'/messages';
        
        $params = array(
            //"from" => "info",
            'to' => explode(',',$to),
            'content' => $msg,
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => array(
                'Authorization: '.$apikey,
                'Content-Type: application/json',
                'Accept: application/json'
            ) ,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        $decoded = json_decode($response);
        return $decoded;
    }
	
    function SEND_SMS_Plivo($apikey, $url, $from, $msg, $to){
        return array();
    }

    $explode_numbers = array_filter(array_map('trim', explode("\n", $to)));
    $to_formatted = implode(",", $explode_numbers);

    if($site_select != 'clickatell'){
        $msg_encoded = json_encode($msg);
    }else{
        $msg_encoded = $msg;
    }
    function processText($text)
    {
        $text = strip_tags($text);
        $text = trim($text);
        // $text = htmlspecialchars($text);
        return $text;
    }
    $output = '';
    $message_ready = processText($msg_encoded);

    $output .= '<br><b>Sent in BULK (Check Logs in a few for results) </b><br>';
    $output .=  '<b>From:</b> ' . $from;
    $output .=  '<br><b>Message: </b>';
    $output .= print_r($message_ready,1);
    $output .=  '<br><b>To (List): </b>';
    $output .= $to_formatted;
    $output .=  '<br>';
	
    $response = array();
    if($site_select == 'clickatell'){
        $response['api_response'] = SEND_SMS_Clickatell($apikey, $url, $from, $message_ready, $to_formatted);
    }
    else if($site_select == 'plivo'){
        $response['api_response'] = SEND_SMS_Plivo($apikey, $url, $from, $message_ready, $to_formatted);
    }
    else{
        $response['api_response'] = SEND_SMS($apikey, $url, $from, $message_ready, $to_formatted);
    }
    $response['html'] = $output;

    // $times_api_called = 0;
    // foreach ($exploded_num as $x => $number) {
    //     SEND_SMS($apikey, $url, $from, $msg, $to);
    //     $times_api_called+=1;
    //     //$aaa ="passworrd".$twilio_aed; $encrypter=/*add pass to func*/"five_to";
    // }

    // echo '<br><br><b>TOTAL API CALLS: '. $times_api_called .'.</b><br>';
    // echo '<b>Numbers: </b> <br>';
    // print_r($exploded_num);
    header('Content-Type: application/json');
    echo json_encode($response);
}