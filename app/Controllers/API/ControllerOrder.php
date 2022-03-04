<?php
namespace App\Controllers\API;

class ControllerOrder extends \App\Controllers\BaseController
{
    public function addOrder($request, $response, array $args)
    {
        $res = array();
        $params = json_decode(file_get_contents('php://input'), true);
        try {
            $oModel = \Model::factory('\App\Models\Order')->create();
            $res = $oModel->saveData($params, 'insert');
        } catch (\Exception $ex) {
            $res['error'] = $ex->getMessage();
        }
        return $this->withJson($response, $res);
    }

    public function getOrder($request, $response, $args)
    {
        $res = array();
        try {
            $oModel = \Model::factory('\App\Models\Order')->create()->getRecord($args['orderid']);
            $res['header'] = array('code'=>1, 'message'=>'Success');
            $res['body'] = $oModel->as_array();
        } catch (\Exception $ex) {
            $res['header'] = array('code'=>101, 'message'=>'No data found!');
            $res['body'] = $ex->getMessage();
        }

        return $this->withJson($response, $res);
    }
}
