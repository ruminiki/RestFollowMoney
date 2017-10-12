<?php

namespace Models;

class MovementsInvoice extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'movimentosFatura';
    protected $primaryKey = ['movimento', 'fatura'];
    public $incrementing = false;
    
}