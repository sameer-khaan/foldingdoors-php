<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Helper{
    public static function d($mParam, $bExit = 0, $bVarDump = 0, $echoInFile = 0,$fileName = null) {
        
        ob_start();
        if(!$echoInFile || !empty($fileName))
            print self::get_back_trace("\n");
        
        if (!$bVarDump) {
            print_r($mParam);
        } else {
            var_dump($mParam);
        }
      
        $sStr = htmlspecialchars(ob_get_contents());
        ob_clean();
        if ($echoInFile) {
            if (!empty($fileName)) {
                file_put_contents(DIR_LOGS . $fileName.'.' . date('d-m-Y') . '.log', $sStr, FILE_APPEND);
            }else{
                $dbgTrace = debug_backtrace();
                $dbgInfo = $dbgTrace[1];
                $oModel = \ORM::for_table('general_logs')->create();
                $oModel->content = $sStr;
                $oModel->file = $dbgInfo['file'];
                $oModel->line = $dbgInfo['line'];
                $oModel->function = $dbgInfo['function'];
                $oModel->created_on = date('Y-m-d H:i:s');
                $oModel->save();
                // file_put_contents(DIR_LOGS . 'd_' . date('d-m-Y') . '.log', $sStr, FILE_APPEND);
            }
        } else {
            echo '<hr><pre>' . $sStr . '</pre><hr>';
        }
        if ($bExit)
            exit;
    }

    public static function editor_log($user, $mParam, $bExit = 0, $bVarDump = 0, $echoInFile = 0,$fileName = null) {
        
        ob_start();
        if(!$echoInFile || !empty($fileName))
            print self::get_back_trace("\n");
        
        if (!$bVarDump) {
            print_r($mParam);
        } else {
            var_dump($mParam);
        }
      
        $sStr = htmlspecialchars(ob_get_contents());
        ob_clean();
        if ($echoInFile) {
            if (!empty($fileName)) {
                file_put_contents(DIR_LOGS . $fileName.'.' . date('d-m-Y') . '.log', $sStr, FILE_APPEND);
            }else{
                $dbgTrace = debug_backtrace();
                $dbgInfo = $dbgTrace[1];
                $oModel = \ORM::for_table('editor_api_logs')->create();
                $oModel->content = $sStr;
                $oModel->file = $dbgInfo['file'];
                $oModel->line = $dbgInfo['line'];
                $oModel->function = $dbgInfo['function'];
                $oModel->created_on = date('Y-m-d H:i:s');
                $oModel->created_by = $user;
                $oModel->save();
            }
        } else {
            echo '<hr><pre>' . $sStr . '</pre><hr>';
        }
        if ($bExit)
            exit;
    }

    public static function makeSlug($string) {
       $string = str_replace(' ', '-', strtolower($string)); // Replaces all spaces with hyphens.

       return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
    
    public static function get_back_trace($NL = "\n") {
        $dbgTrace = debug_backtrace();
        $dbgMsg = "Trace[";
        foreach ($dbgTrace as $dbgIndex => $dbgInfo) {
            if ($dbgIndex > 0 && isset($dbgInfo['file'])) {
                $dbgMsg .= "\t at $dbgIndex  " . $dbgInfo['file'] . " (line {$dbgInfo['line']}) -> {$dbgInfo['function']}(" . count($dbgInfo['args']) . ")$NL";
            }
        }
        $dbgMsg .= "]" . $NL;
        return $dbgMsg;
    }

    public static function mail($to,$subject,$body){ 

        $transport = (new \Swift_SmtpTransport(SMTP_HOST, SMTP_PORT, 'ssl'))
            ->setUsername(SMTP_USER)
            ->setPassword(SMTP_PASS);

        $mailer = new \Swift_Mailer($transport);
        // Create a message
        $message = (new \Swift_Message($subject))
            ->setFrom(['info@hazwoper-osha.com' => 'HAZWOPER OSHA'])
            ->setTo([$to])
            ->setBody($body)
            ->setContentType('text/html');

        // Send the message
        $result = $mailer->send($message);
    }

    public static function gmail($to,$subject,$body){ 

        // Create a message
        $message = (new \Swift_Message($subject))
            ->setFrom(['info@hazwoper-osha.com' => 'HAZWOPER OSHA'])
            ->setTo([$to])
            ->setBody($body)
            ->setContentType('text/html');

        // Send the message
        //$result = $mailer->send($message);
        $msg = self::base64url_encode($message);
        
        $client = self::getClient();        

        $service = new \Google_Service_Gmail($client);
        $message = new \Google_Service_Gmail_Message();
        $message->setRaw($msg);
        $message = $service->users_messages->send('me', $message);
    }

    public static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function getClient(){    
        $client = new \Google_Client();
        
        $client->setApplicationName('hazwoper-social-login');
        $client->setScopes(\Google_Service_Gmail::MAIL_GOOGLE_COM);    
        $client->setAuthConfig(DIR_MAIN.'/api/credentials.json');
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setIncludeGrantedScopes(true);
        $client->setRedirectUri(WP_MAIN_SERVER."/lms/api/get_client");
        $client->setPrompt('select_account consent');
        
        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'token.json';
        
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }
        
        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));
                print_r($authCode);
                exit();
                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new \Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    public static function phpmailler($to,$subject,$body){
        
        $mail = new PHPMailer(); // create a new object
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth = true; // authentication enabled
        $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587; // or 587
        $mail->IsHTML(true);
        $mail->Username = 'info@hazwoper-osha.com';
        $mail->Password = 'ctwxdjgftqrqqmhd';
       // $mail->Password = 'vsnakwnzwuoukwzr';
        $mail->SetFrom("info@hazwoper-osha.com");
        $mail->Subject =$subject;
        $mail->Body = $body;
        $mail->AddAddress($to);

        if(!$mail->Send()) {
            $result['error'] = "Mailer Error: " . $mail->ErrorInfo;
        } else {
            $result['Success'] =  "Message has been sent";
        }
        return $result;
    } 

    public static function getUserServerInfo($request){
        $aRes = [];
        $server = $request->getServerParams();
        $aRes['ip'] = $server['REMOTE_ADDR'];
        $aRes['user_agent'] = $server['HTTP_USER_AGENT'];
        return $aRes;
    }
}