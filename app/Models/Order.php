<?php 
namespace App\Models;

class Order extends \Model {

	use Validation;
	public static $_id = 'id';
	public static $_table = 'orders';

	//validation rules
	protected static $_rules = [
		'id' => ['integer', 'required'],
		'quote_no' => ['string', 'required'],
		'name' => ['string', 'required'],
		'email' => ['string', 'required'],
		'phone' => ['string', 'required']
	];

	public function getRecord($orderid)
	{
		return \ORM::for_table(self::$_table)->where('quote_no',$orderid)->find_one();
	}

	public function getRecordAll()
	{
		return \ORM::for_table(self::$_table)->find_array();
	}

	public function payments()
	{
		return $this->has_many('\App\Models\Payment', 'order_id');
	}

	public function saveData($params, $type)
	{
		$this->validate('\App\Models\Order', self::$_rules, self::$_id, $params, $type);

		$message = ($type == 'insert') ? 'submitted' : 'updated';
		$this->set(array(
			'quote_no' => $params['orderid'],
			'price' => $params['price'],
			'name' => $params['yourName'],
			'email' => $params['email'],
			'phone' => $params['telephone'],
			'postcode' => $params['postcode'],
			'data' => json_encode($params)
		));
		$this->save();

		$res['success'] = ucwords("order $message successfully");
		return $res;
	}
}