<?php

class Helper{

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

    public static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function getUserServerInfo($request) {
        $aRes = [];
        $server = $request->getServerParams();
        $aRes['ip'] = $server['REMOTE_ADDR'];
        $aRes['user_agent'] = $server['HTTP_USER_AGENT'];
        return $aRes;
    }
}