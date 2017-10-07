<?php

require_once("dao/DB.php");

class PaymentForm{

    const TABLE_NAME='formaPagamento';

    public static function listByUser($user){
        $result = DB::listByUser(PaymentForm::TABLE_NAME, $user);
        return PaymentForm::resultToArray($result);
    }

    public static function findByID($id){
        $result = DB::findById(PaymentForm::TABLE_NAME, $id);
        return PaymentForm::rowToObject($result);
    }

    public static function insert($vo){
        $id = DB::insert(PaymentForm::TABLE_NAME, ['descricao', 'usuario'], [$vo->descricao, $vo->usuario]);
        $vo->id = $id;
        return $vo;
    }

    public static function update($vo){
        DB::update(PaymentForm::TABLE_NAME, ['descricao' => $vo->descricao], $vo->id);
        return $vo;
    }

    public static function delete($id){
        return DB::delete(PaymentForm::TABLE_NAME, $id);
    }

    public static function resultToArray($result){
        $list = array();
    
        foreach ($result as $key => $value) {
            array_push($list, PaymentForm::rowToObject($value));
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