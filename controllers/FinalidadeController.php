<?php

include_once("dao/DB.php");

class FinalidadeController{

    const TABLE_NAME='finalidade';

    public static function listByUser($user){
        $result = DB::listByUser(FinalidadeController::TABLE_NAME, $user);
        return FinalidadeController::resultToArray($result);
    }

    public static function resultToArray($result){
        $list = array();
    
        foreach ($result as $key => $value) {
            array_push($list, FinalidadeController::rowToObject($value));
        }

        return $list;
    }
        
    public static function rowToObject($row){
        $entity            = new stdClass();
        $entity->id        = $row['id'];
        $entity->descricao = $row['descricao'];
        return $entity;
    }

}
?>