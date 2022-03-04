<?php 
namespace App\Models;

trait Validation {

	public function validate($_model, $_rules, $_id, $params, $type, $cond = null) {
		$fields = $_rules;
		//loop through rules
		foreach($fields as $key => $value) {
			//if field found in validation
			if (array_key_exists($key, $params)) {
				//validate incoming value
				$field_val = $params[$key];
				//if rules contain required
				if (in_array('required', $value)) {
					if(empty($field_val))
						throw new \Exception("$key is required field.");
				}
				//if rules contain unique
				if (in_array('unique', $value)) {
					if($type == 'update'){
						if($cond != null)
							$is_exist = \Model::factory($_model)->where_not_equal($cond)->filter('is_unique', $key, $field_val)->find_array();
						else
							$is_exist = \Model::factory($_model)->where_not_equal($_id, $params[$_id])->filter('is_unique', $key, $field_val)->find_array();
					}
					else{
						$is_exist = \Model::factory($_model)->filter('is_unique', $key, $field_val)->find_array();
					}
					
					if($is_exist)
						throw new \Exception("$key should be unique.");
				}
				//if rules contain string
				if (in_array('string', $value)) {
					if(!is_string($field_val))
						throw new \Exception("$key should be string only.");
				}
				//if rules contain integer
				// if (in_array('integer', $value)) {
				// 	if(!is_int($field_val))
				// 		throw new \Exception("$key should be integer only.");
				// }
				//if rules contain integer
				if (in_array('float', $value)) {
					if(!is_float($field_val))
						throw new \Exception("$key should be float only.");
				}
			}
		}
    }

	public static function is_unique($orm, $field, $value) {
        return $orm->where($field, $value);
    }

	public function timestamp($params, $type) {
		if($type == 'update') {
			$this->set(array(
				'updated_on' => date("Y-m-d H:i:s"),
			));
			if(isset($params['updated_by']) && !empty($params['updated_by'])) {
				$this->set(array(
					'updated_by' => $params['updated_by'],
				));
			}
		}
		else {
			$this->set(array(
				'created_on' => date("Y-m-d H:i:s"),
				'updated_on' => date("Y-m-d H:i:s")
			));
			if(isset($params['created_by']) && !empty($params['created_by'])) {
				$this->set(array(
					'created_by' => $params['created_by'],
					'updated_by' => $params['created_by'],
				));
			}
		}
	}
}