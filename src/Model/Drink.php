<?php

namespace Src\Model;

use Exception;
use Src\Model;
use Src\DB\Sql;
use Src\Helpers\Helper;

class Drink extends Model
{

    public function drinkCreate($ml,$id){
        $sql = new Sql();
        $idUser = $id;

		try {
			$results = $sql->select("CALL sp_drink_create(:iduserp,:amountp)", array(
				":iduserp" => (int)$id,
				":amountp" => (int)$ml,
			));
		} catch (Exception $e) {
			$error = array(
				'error' => 'Exception: ',  $e->getMessage(),
			);
			http_response_code(400);
			echo json_encode($error);
		}
	}
	
	public function getHistory($id){
		$sql = new Sql();
		
		try {
			$results = $sql->select(
				"SELECT users.name, users.iduser ,amount as quatity_ML, request as day 
				from drinks 
				join users on drinks.iduser = users.iduser 
				where users.iduser = :iduserp and drinks.situation = 1", array(
				":iduserp" => (int)$id
			));

			if(count($results) == 0){

				Helper::badRequest('User not Drink');
			}

			echo json_encode($results);
		} catch (Exception $e) {
			$error = array(
				'error' => 'Exception: ',  $e->getMessage(),
			);
			http_response_code(400);
			echo json_encode($error);
		}
	}

	public function getRanking(){
		$sql = new Sql();
		
		try {
			$results = $sql->select("SELECT distinct(iduser) as user,(select sum(amount) from drinks where iduser = user) as ML from drinks where date(request) = CURDATE() ORDER BY  ML DESC");

			if(count($results) == 0){

				Helper::badRequest('Users not Drink');
			}

			echo json_encode($results);
		} catch (Exception $e) {
			$error = array(
				'error' => 'Exception: ',  $e->getMessage(),
			);
			http_response_code(400);
			echo json_encode($error);
		}
	}
	

    
}