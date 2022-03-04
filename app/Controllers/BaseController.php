<?php
namespace App\Controllers;

use Psr\Container\ContainerInterface;

class BaseController {

    protected $app;
    protected $view;

    public function __construct(ContainerInterface $container) {
        $this->app = $container;
        //$this->view = $container->get('view');
    }

    public function withJson($response, $array) {
        $response->getBody()->write(json_encode($array));
        $response = $response->withHeader('Content-Type','application/json');
        return $response;
    }

    public function getQueryString() {
        $aUrl = array();
        if ($this->app->request()->get()) {
            foreach ($this->app->request()->get() as $sKey => $sValue) {
                $aUrl[] = $sKey . '=' . $sValue;
            }
        }
        if (empty($aUrl))
            return '';

        return '?' . join('&', $aUrl);
    }

    public function getReplaceableContent($data, $emailSubject = '', $emailbody = '') {
		$res = $keys = array();
        $content = '';

        //get replaceable keys from db
        $tModel = \Model::factory('\App\Models\EmailType')->where_raw('search_replace IS NOT NULL')->find_array();
        foreach($tModel as $value) {
            $content .= trim(trim($value['search_replace'],' '),',').',';
        }
        $content = trim($content,',');
        $res = explode(',',$content);

        //set to key value pair
        for($i=0; $i < count($res); $i++) {
            $val = trim($res[$i], ' ');
            $val = trim($val, '[');
            $val = trim($val, ']');

            //search with data and set value if found
            if (array_key_exists(strtolower($val),$data)) {
                $keys[$val] = $data[strtolower($val)];
                $emailSubject = str_replace("[$val]",$data[strtolower($val)],$emailSubject);
                $emailbody = str_replace("[$val]",$data[strtolower($val)],$emailbody);
            }
            else {
                $keys[$val] = '';
            }
        }

        $newArr['content'] = $keys;
        $newArr['subject'] = $emailSubject;
        $newArr['body'] = $emailbody;
        return $newArr;
	}

    public function sendmail($to, $key, $data) {
		$res = array();
		try {
            $oModel = \Model::factory('\App\Models\EmailTemplates')->where('email_key', $key)->where('status', '1')->find_one();
            if($oModel){
                $content = $this->getReplaceableContent($data, $oModel->subject, $oModel->body);
                \Helper::gmail($to,$content['subject'],$content['body']);
                $res['success'] = "Email sent successfully";
            }
		} catch(\Exception $ex) {
			$res['error'] = $ex->getMessage();
            \Helper::d(array($ex->getMessage()." ".$ex->getTraceAsString(),$ex->getFile(),$ex->getLine()),0,0,1);
		}

		return $res;
	}

    public function redirect($url) {
        header("Location: ".HTTP_MAIN_SERVER.$url);
        exit();
    }
}

?>