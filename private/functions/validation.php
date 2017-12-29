<?php

function validate_data($requestdata, $fields, &$error)
{
	$validated_data = [];
	foreach($fields as $field => $field_info)
	{
		$field_required = false;
		if(isset($field_info["req"]))
		{
			$field_required = $field_info["req"];
		}
		$field_type = "string";
		if(isset($field_info["type"]))
		{
			$field_type = $field_info["type"];
		}
		$field_minlength = null;
		if(isset($field_info["minlength"]))
		{
			$field_minlength = $field_info["minlength"];
		}
		$field_maxlength = null;
		if(isset($field_info["maxlength"]))
		{
			$field_maxlength = $field_info["maxlength"];
		}
		$field_name = $field;
		if(isset($field_info["rename"]))
		{
			$field_name = $field_info["rename"];
		}
		
		if(isset($requestdata[$field]))
		{
			$value = $requestdata[$field];
			
			//check field length
			if($field_minlength !== null && strlen($value) < $field_minlength)
			{
				$error = $field." field cannot be shorter than ".$field_minlength." characters";
				return null;
			}
			if($field_maxlength !== null && strlen($value) > $field_maxlength)
			{
				$error = $field." field cannot be longer than ".$field_maxlength." characters";
				return null;
			}
			
			if(is_array($field_type))
			{
				//value can be any one from an array of values
				$foundMatch = false;
				foreach($field_type as $field_enum)
				{
					if($value===$field_enum)
					{
						$foundMatch = true;
						break;
					}
				}
				if(!$foundMatch)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$validated_data[$field_name] = $value;
			}
			else if($field_type=="string")
			{
				$validated_data[$field_name] = ''.$value;
			}
			else if($field_type=="float")
			{
				if(filter_var($value, FILTER_VALIDATE_FLOAT)===false)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$validated_data[$field_name] = floatval($value);
			}
			else if($field_type=="ufloat")
			{
				if(filter_var($value, FILTER_VALIDATE_FLOAT)===false)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$newval = floatval($value);
				if($newval < 0)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$validated_data[$field_name] = $newval;
			}
			else if($field_type=="integer")
			{
				if(filter_var($value, FILTER_VALIDATE_INT)===false)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$validated_data[$field_name] = intval($value);
			}
			else if($field_type=="uinteger")
			{
				if(filter_var($value, FILTER_VALIDATE_INT)===false)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$newval = intval($value);
				if($newval < 0)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$validated_data[$field_name] = $newval;
			}
			else if($field_type=="boolean")
			{
				if(filter_var($value, FILTER_VALIDATE_BOOLEAN)===false)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$validated_data[$field_name] = boolval($value);
			}
			else if($field_type=="email")
			{
				if(filter_var($value, FILTER_VALIDATE_EMAIL)===false)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$validated_data[$field_name] = ''.$value;
			}
			else
			{
				$error = "unknown field_type ".$field_type;
				return null;
			}
		}
		else if($field_required)
		{
			$error = "missing required field ".$field;
			return null;
		}
	}
	return $validated_data;
}

