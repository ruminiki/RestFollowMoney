<?php

require_once("dao/DB.php");

class CreditCard{

    const TABLE_NAME='cartaoCredito';

    public static function listByUser($user){
        $result = DB::listByUser(CreditCard::TABLE_NAME, $user);
        return CreditCard::resultToArray($result);
    }

    public static function findByID($id){
        $result = DB::findById(CreditCard::TABLE_NAME, $id);
        return CreditCard::rowToObject($result);
    }

    public static function insert($vo){
        return DB::insertNew(CreditCard::TABLE_NAME, 
            ['descricao', 
             'limite', 
             'dataFatura', 
             'dataFechamento', 
             'usuario'], 
            [$vo->descricao, 
             $vo->limite, 
             $vo->dataFatura, 
             $vo->dataFechamento, 
             $vo->usuario]);
    }

    public static function update($vo){
        return DB::update(CreditCard::TABLE_NAME, 
            ['descricao'      => $vo->descricao,
             'limite'         => $vo->limite,
             'dataFatura'     => $vo->dataFatura,
             'dataFechamento' => $vp->dataFechamento], 
            $vo->id);
    }

    public static function delete($id){
        return DB::delete(CreditCard::TABLE_NAME, $id);
    }

    public static function resultToArray($result){
        $list = array();
    
        foreach ($result as $key => $value) {
            array_push($list, CreditCard::rowToObject($value));
        }
        return $list;
    }
        
    public static function rowToObject($row){
        $entity                 = new stdClass();
        $entity->id             = $row['id'];
        $entity->descricao      = $row['descricao'];
        $entity->limite         = $row['limite'];
        $entity->dataFatura     = $row['dataFatura'];
        $entity->dataFechamento = $row['dataFechamento'];
        $entity->usuario        = $row['usuario'];
        return $entity;
    }

}

?>