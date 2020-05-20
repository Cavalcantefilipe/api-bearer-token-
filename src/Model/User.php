<?php

namespace Src\Model;

use Exception;
use \Src\Model;
use \Src\DB\Sql;
use Src\Helpers\Helper;

class User extends Model
{

	const SESSION = "User";
	const Token = "Token";

	protected $fields = ["iduser", "email", "name", "password","total"];

	public function createUser()
	{
		$sql = new Sql();
		try {
			$results = $sql->select("CALL sp_users_create(:name,:password,:email)", array(
				":name" => utf8_decode($this->getname()),
				":password" => User::getPasswordHash($this->getpassword()),
				":email" => $this->getemail()
			));
		} catch (Exception $e) {
			$error = array(
				'error' => 'Exception: ',  $e->getMessage(),
			);
			http_response_code(400);
			echo json_encode($error);
		}
	}

	private function gerateToken()
	{
		$token = bin2hex(random_bytes(64));
		$_SESSION[User::Token] = $token;
	}

	public static function getFromSession()
	{
		$user = new User();
		if (isset($_SESSION[User::SESSION]) && (int) $_SESSION[User::SESSION]['iduser'] > 0) {

			$user->setData($_SESSION[User::SESSION]);
		}
		return $user;
	}

	public static function checkLogin($token)
	{

		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int) $_SESSION[User::SESSION]["iduser"] > 0
		) {
			return false;
		} else {

			if ($_SESSION[User::Token] == $token) {
				return true;
			} else {

				return false;
			}
		}
	}

	public static function login($email, $password)
	{

		$db = new Sql();

		$results = $db->select(
			"SELECT users.iduser, users.email,
			users.name, drinktotal.total as drink_quantity,
			drinktotal.calls as drink_counter,users.password
			FROM users 
			join drinktotal on users.iduser = drinktotal.iduser
			WHERE email = :email",
			array(
				":email" => $email
			)
		);
		if (count($results) === 0) {
			$error = array(
				'error' => utf8_encode('User not find')
			);
			http_response_code(400);
			echo json_encode($error);
			exit;
		}

		$data = $results[0];

		if (password_verify($password, $data["password"]) === true) {
			$user = new User();

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();
			User::gerateToken();

			$data = array(
				"Token" => $_SESSION[User::Token],
				"User" => $results[0],
			);
			unset($data["User"]['password']);
			echo json_encode($data);
		} else {

			$error = array(
				'error' => utf8_encode('password incorrect')
			);
			http_response_code(400);
			echo json_encode($error);
		}
	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;
		$_SESSION[User::Token] = NULL;
	}


	public  function getUser($idUser)
	{
		$sql = new Sql();
		try {
			$result = $sql->select(
				"SELECT users.iduser, users.email,
				users.name, drinktotal.total as drink_quantity,
				drinktotal.calls as drink_counter
				FROM users 
				join drinktotal on users.iduser = drinktotal.iduser 
				WHERE users.iduser = :ID",
					array(
						":ID" => $idUser
					)
				);

			if (isset($result[0])) {
				$result[0]['name'] = utf8_encode($result[0]['name']);
				echo json_encode($result[0]);
			} else {
				$error = array(
					'error' => utf8_encode('User not find')
				);
				http_response_code(400);
				echo json_encode($error);
			}
		} catch (Exception $e) {
			$error = array(
				'error' => 'Exception: ',  $e->getMessage(),
			);
			http_response_code(400);
			echo json_encode($error);
		}
	}

	public  function getUsers()
	{
		$sql = new Sql();

		try {

			$result = $sql->select(
					"SELECT users.iduser, users.email,
					users.name, drinktotal.total as drink_quantity,
					drinktotal.calls as drink_counter
					FROM users 
					join drinktotal on users.iduser = drinktotal.iduser"
				);
			if (isset($result[0])) {
				echo json_encode($result);
			} else {
				$error = array(
					'error' => utf8_encode('Users not find')
				);
				http_response_code(400);
				echo json_encode($error);
			}
		} catch (Exception $e) {
			$error = array(
				'error' => 'Exception: ',  $e->getMessage(),
			);
			http_response_code(400);
			echo json_encode($error);
		}
	}

	public function updateUser($id){

		$user = User::getFromSession();
		if($user->getiduser() != $id){

			Helper::badRequest('you dont Update this user');
		}
		$sql = new Sql();
		if($this->getname() === 0){
			$name = (string)$user->getname();
		}else{
			$name = (string)$this->getname();
		}
		if($this->getpassword() === 0){
			$password = (string)$user->getpassword();
		}else{
			$password = (string) User::getPasswordHash($this->getpassword());
		}
		if($this->getemail() === 0){
			$email = (string)$user->getemail();
		}else{
			$email = (string) $this->getemail();
		}


		try {
			$sql->query(
				"UPDATE users 
				SET name = :name ,
				password = :password, 
				email = :email 
				WHERE iduser = :ID ", array(
				":name" => $name,
				":password" =>$password,
				":email" => $email,
				":ID" => (int)$id
			));
		} catch (Exception $e) {
			$error = array(
				'error' => 'Exception: ',  $e->getMessage(),
			);
			http_response_code(400);
			echo json_encode($error);
		}

	}

	public function deleteUser($id){

		$user = User::getFromSession();
		if($user->getiduser() != $id){

			Helper::badRequest('you dont Delete this user');
		}
		$sql = new Sql();

		try {
			$results = $sql->select("CALL sp_users_delete(:id)", array(
				":id" => $id,
			));
		} catch (Exception $e) {
			$error = array(
				'error' => 'Exception: ',  $e->getMessage(),
			);
			http_response_code(400);
			echo json_encode($error);
		}
	}

	public static function getPasswordHash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT, array(
			'cost' => 12
		));
	}
}
