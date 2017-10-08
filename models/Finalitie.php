<?php

require_once("dao/DB.php");

class Finalitie{

    const TABLE_NAME='finalidade';

    public static function listByUser($user){
        $result = DB::listByUser(Finalitie::TABLE_NAME, $user);
        return Finalitie::resultToArray($result);
    }

    public static function listByUserAndLetters($user, $letters){
        global $logger;
        $logger->addInfo('Finalitie:fill by letters: ' . $letters);
        $sql = "select id, descricao, usuario from finalidade where usuario = $user and descricao RLIKE '^[$letters]' order by descricao";
        $result = DB::executeQuery($sql, []);
        return Finalitie::resultToArray($result);
    }

    public static function findByID($id){
        $result = DB::findById(Finalitie::TABLE_NAME, $id);
        return Finalitie::rowToObject($result);
    }

    public static function insert($vo){
        $id = DB::insert(Finalitie::TABLE_NAME, ['descricao', 'usuario'], [$vo->descricao, $vo->usuario]);
        $vo->id = $id;
        return $vo;
    }

    public static function update($vo){
       DB::update(Finalitie::TABLE_NAME, ['descricao' => $vo->descricao], $vo->id);
       return $vo;
    }

    public static function delete($id){
        return DB::delete(Finalitie::TABLE_NAME, $id);
    }

    public static function resultToArray($result){
        $list = array();
    
        foreach ($result as $key => $value) {
            array_push($list, Finalitie::rowToObject($value));
        }
        return $list;
    }
        
    public static function rowToObject($row){
        $entity            = new stdClass();
        $entity->id        = $row['id'];
        $entity->descricao = $row['descricao'];
        $entity->usuario   = $row['usuario'];
        return $entity;
    }

}

?>