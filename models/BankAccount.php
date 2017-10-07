<?php

require_once("dao/DB.php");

class BankAccount{

    const TABLE_NAME     = 'contaBancaria';
    const STATUS_ATIVO   = '1';
    const STATUS_INATIVO = '0';    

    public static function listByUser($user){
        $result = DB::listByUser(BankAccount::TABLE_NAME, $user);
        return BankAccount::resultToArray($result);
    }

    public static function findByID($id){
        $result = DB::findById(BankAccount::TABLE_NAME, $id);
        return BankAccount::rowToObject($result);
    }

    public static function insert($vo){
        $id = DB::insert(BankAccount::TABLE_NAME, 
            ['descricao', 
             'numero', 
             'digito', 
             'situacao', 
             'usuario'], 
            [$vo->descricao, 
             $vo->numero, 
             $vo->digito, 
             $vo->situacao, 
             $vo->usuario]);
        $vo->id = $id;
        return $vo;
    }

    public static function update($vo){
        DB::update(BankAccount::TABLE_NAME, 
            ['descricao' => $vo->descricao,
             'numero'    => $vo->numero,
             'digito'    => $vo->digito,
             'situacao'  => $vo->situacao], 
            $vo->id);
        return $vo;
    }

    public static function delete($id){
        DB::update(BankAccount::TABLE_NAME,['situacao'  => BankAccount::STATUS_INATIVO], $id);
        return $vo;
    }

    public static function resultToArray($result){
        $list = array();
    
        foreach ($result as $key => $value) {
            array_push($list, BankAccount::rowToObject($value));
        }
        return $list;
    }
        
    public static function rowToObject($row){
        $entity            = new stdClass();
        $entity->id        = $row['id'];
        $entity->descricao = $row['descricao'];
        $entity->numero    = $row['numero'];
        $entity->digito    = $row['digito'];
        $entity->situacao  = $row['situacao'];
        $entity->usuario   = $row['usuario'];
        return $entity;
    }

}

?>