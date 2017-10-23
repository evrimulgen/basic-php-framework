<?php

require_once(PRIVATE_DIR."/external/PHPMailer/src/Exception.php");
require_once(PRIVATE_DIR."/external/PHPMailer/src/PHPMailer.php");
require_once(PRIVATE_DIR."/external/PHPMailer/src/SMTP.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class User
{
	const PASS_SALT = "buttSTUFF";

	public $id = null;
	public $firstname = null;
	public $lastname = null;
	public $username = null;
	public $email = null;
	public $validation_code = null;
	public $reset_pw_token = null;

	private function __construct($row)
	{
		$this->id = $row["id"];
		$this->firstname = $row["firstname"];
		$this->lastname = $row["lastname"];
		$this->username = $row["username"];
		$this->email = $row["email"];
		$this->validation_code = $row["validation_code"];
		$this->reset_pw_token = $row["reset_pw_token"];
	}

	public static function get_user_by_id($id)
	{
		global $mysql;
		if(!is_numeric($id))
		{
			return null;
		}
		$result = $mysql->query('select * from user where id='.$id);
		if($result==false)
		{
			return null;
		}
		else if($result->num_rows==0)
		{
			$result->num_rows==0;
		}
		$row = $result->fetch_assoc();
		$result->free();
		return new User($row);
	}
	
	public static function get_user_by_username($username)
	{
		global $mysql;
		$escaped_username = $mysql->real_escape_string(strtolower($username));
		$result = $mysql->query('select * from user where lcase(username)="'.$escaped_username.'"');
		if($result==false)
		{
			return null;
		}
		else if($result->num_rows==0)
		{
			$result->free();
			return null;
		}
		$row = $result->fetch_assoc();
		$result->free();
		return new User($row);
	}

	public static function get_user_by_email($email)
	{
		global $mysql;
		$escaped_email = $mysql->real_escape_string(strtolower($email));
		$result = $mysql->query('select * from user where email="'.$escaped_email.'"');
		if($result==false)
		{
			return null;
		}
		else if($result->num_rows==0)
		{
			$result->free();
			return null;
		}
		$row = $result->fetch_assoc();
		$result->free();
		return new User($row);
	}

	public static function signup($signup_data, &$error = null)
	{
		global $mysql;
		
		$required_fields = ["firstname", "lastname", "email", "username", "password"];
		foreach($required_fields as $field)
		{
			if(empty($signup_data[$field]))
			{
				$error = 'Missing required parameter "'.$field.'"';
				return false;
			}
		}
		
		$firstname = $signup_data["firstname"];
		$lastname = $signup_data["lastname"];
		$email = $signup_data["email"];
		$username = $signup_data["username"];
		$password = $signup_data["password"];

		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$error = "Invalid Email Address";
			return false;
		}

		if(strlen($password) < 5 || strlen($password) > 24)
		{
			$error = "Password must be between 8 and 24 characters";
			return false;
		}

		$escaped_email = $mysql->real_escape_string(strtolower($email));
		$escaped_username = $mysql->real_escape_string($username);
		$escaped_lcase_username = $mysql->real_escape_string(strtolower($username));
		$hashed_password = password_hash($password.self::PASS_SALT, PASSWORD_DEFAULT);
		$escaped_password = $mysql->real_escape_string($hashed_password);
		
		//check if email exists
		$result = $mysql->query('select email from user where email="'.$escaped_email.'"');
		if($result === false)
		{
			$error = "internal error";
			return false;
		}
		else if($result->num_rows > 0)
		{
			$error = "An account with that email already exists";
			$result->free();
			return false;
		}
		//check if username exists
		$result = $mysql->query('select username from user where lcase(username)="'.$escaped_lcase_username.'"');
		if($result === false)
		{
			$error = "internal error";
			return false;
		}
		else if($result->num_rows > 0)
		{
			$error = "An account with that username already exists";
			$result->free();
			return false;
		}
		
		$escaped_firstname = $mysql->real_escape_string($firstname);
		$escaped_lastname = $mysql->real_escape_string($lastname);
		
		//actually add the user
		$validation_code = random_hexstring(32);
		$result = $mysql->query('insert into user (firstname, lastname, username, email, password, validation_code) values ("'.
								$escaped_firstname.'", "'.
								$escaped_lastname.'", "'.
								$escaped_username.'", "'.
								$escaped_email.'", "'.
								$escaped_password.'", "'.
								$validation_code.'")');
		if($result === false)
		{
			$error = "internal error";
			return false;
		}
		$user = self::get_user_by_email($email);
		$user->email_validation_code($error);
		return true;
	}

	private static function start_session($username, &$error)
	{
		$user = self::get_user_by_username($username);
		if($user == null)
		{
			$error = "No account exists with this email";
			return false;
		}

		session_start();
		$_SESSION["user_id"] = $user->id;
		$_SESSION["user_email"] = $user->email;

		$GLOBALS["user"] = $user;
		return true;
	}

	public static function login($username, $password, &$error = null)
	{
		global $mysql;
		if(!empty(session_id()))
		{
			$error = "user already logged in";
			return false;
		}
		$escaped_username = $mysql->real_escape_string(strtolower($username));
		$result = $mysql->query('select * from user where email="'.$escaped_username.'"');
		if($result == false)
		{
			$error = "internal error";
			return false;
		}
		else if($result->num_rows == 0)
		{
			$error = "Invalid username / password";
			$result->free();
			return false;
		}
		$row = $result->fetch_assoc();
		if(!password_verify($password.self::PASS_SALT, $row["password"]))
		{
			$error = "Invalid username / password";
			$result->free();
			return false;
		}
		else if(!empty($row["validation_code"]))
		{
			$error = 'Check your inbox or spam for a validation link';
			$result->free();
			return false;
		}
		$result->free();
		if(!self::start_session($username, $error))
		{
			return false;
		}
		return true;
	}

	public static function resume_session()
	{
		if(empty($_COOKIE["PHPSESSID"]))
		{
			return false;
		}
		session_start();
		if(!isset($_SESSION["user_id"]) || !isset($_SESSION["user_email"]))
		{
			session_destroy();
			return false;
		}
		$GLOBALS["user"] = self::get_user_by_id($_SESSION["user_id"]);
		return true;
	}

	public static function logout()
	{
		session_destroy();
	}

	public static function validate_email($email, $validation_code, &$error = null)
	{
		global $mysql;
		$user = self::get_user_by_email($email);
		if($user == null)
		{
			$error = "No account exists with this email";
			return false;
		}
		else if(empty($user->validation_code))
		{
			$error = "Account is already validated";
			return false;
		}
		else if($validation_code != $user->validation_code)
		{
			$error = "Invalid validation code";
			return false;
		}
		$escaped_email = $mysql->real_escape_string(strtolower($email));
		$result = $mysql->query('update user set validation_code=null where email="'.$escaped_email.'"');
		if($result === false)
		{
			$error = "internal error";
			return false;
		}
		$result->free();
		if(!self::start_session($user->username, $error))
		{
			return false;
		}
		return true;
	}

	public function email_validation_code(&$error = null)
	{
		if(empty($this->validation_code))
		{
			$error = "email already validated";
			return false;
		}

		$mail = new PHPMailer(true);
		try
		{
			$mail->setFrom("noreply@fridayfund.org", "NoReply Friday Fund");
			$mail->addAddress($this->email, "You, the Person");

			$validate_url = 'http://themovesapp.com/validate-email?email='.$this->email.'&code='.$this->validation_code;

			$mail->Subject = "Validate your email address";
			$mail->AltBody = "Go here to validate your email: ".$validate_url;
			$mail->Body = '<a href="'.htmlspecialchars($validate_url).'">Click here to validate your email</a>';
			$mail->send();
			return true;
		}
		catch(Exception $e)
		{
			error_log("Error sending validation email: ".$mail->ErrorInfo);
			$error = "error sending validation email";
			return false;
		}
	}
	
	public function email_reset_password_link(&$error = null)
	{
		global $mysql;
		$reset_pw_token = random_hexstring(32);
		$result = $mysql->query('update user set reset_pw_token="'.$mysql->real_escape_string($reset_pw_token).'" where id='.$this->id);
		if($result==false)
		{
			$error = "internal error";
			return false;
		}
		$this->reset_pw_token = $reset_pw_token;
		
		$mail = new PHPMailer(true);
		try
		{
			$mail->setFrom("noreply@themovesapp", "NoReply The Moves");
			$mail->addAddress($this->email);

			$password_reset_url = 'http://themovesapp.com/reset-password?email='.$this->email.'&reset_pw_token='.$this->reset_pw_token;

			$mail->Subject = "Reset your password";
			$mail->AltBody = "Go here to reset your password: ".$password_reset_url;
			$mail->Body = '<a href="'.htmlspecialchars($password_reset_url).'">Click here to reset your password</a>';
			$mail->send();
			return true;
		}
		catch(Exception $e)
		{
			error_log("Error sending password reset email: ".$mail->ErrorInfo);
			$error = "error sending password reset email";
			return false;
		}
	}
	
	public function reset_password($password, $reset_pw_token, &$error = null)
	{
		global $mysql;
		if(empty($this->reset_pw_token))
		{
			$error = "password reset token is either invalid or expired";
			return false;
		}
		if($this->reset_pw_token != $reset_pw_token)
		{
			$error = "password reset token is either invalid or expired";
			return false;
		}
		$hashed_password = password_hash($password.self::PASS_SALT, PASSWORD_DEFAULT);
		$escaped_password = $mysql->real_escape_string($hashed_password);
		$result = $mysql->query('update user set password="'.$escaped_password.'", reset_pw_token=NULL where id='.$this->id);
		if($result == false)
		{
			$error = "internal error";
			return false;
		}
		return true;
	}
}

?>
