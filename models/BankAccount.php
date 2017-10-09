<?php

namespace Models;

class BankAccount extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'contaBancaria';
    const STATUS_ATIVO   = '1';
    const STATUS_INATIVO = '0';    

}

?>