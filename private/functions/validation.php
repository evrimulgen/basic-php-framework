<?php

function validate_data($requestdata, $fields, &$error)
{
	$validated_data = [];
	foreach($fields as $field => $field_info)
	{
		$field_required = $field_info["req"] ?? false;
		$field_type = $field_info["type"] ?? "string";
		$field_minlength = $field_info["minlength"] ?? null;
		$field_maxlength = $field_info["maxlength"] ?? null;
		
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
				$validated_data[$field] = $value;
			}
			else if($field_type=="string")
			{
				$validated_data[$field] = ''.$value;
			}
			else if($field_type=="float")
			{
				if(filter_var($value, FILTER_VALIDATE_FLOAT)===false)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$validated_data[$field] = floatval($value);
			}
			else if($field_type=="integer")
			{
				if(filter_var($value, FILTER_VALIDATE_INT)===false)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$validated_data[$field] = intval($value);
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
				$validated_data[$field] = intval($value);
			}
			else if($field_type=="bool")
			{
				if(filter_var($value, FILTER_VALIDATE_BOOLEAN)===false)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$validated_data[$field] = boolval($value);
			}
			else if($field_type=="email")
			{
				if(filter_var($value, FILTER_VALIDATE_EMAIL)===false)
				{
					$error = "invalid value ".$value." for field ".$field;
					return null;
				}
				$validated_data[$field] = ''.$value;
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

?>
