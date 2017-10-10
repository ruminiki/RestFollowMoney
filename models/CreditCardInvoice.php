<?php

namespace Models;

use Models\Movement as Movement;

class CreditCardInvoice extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'fatura';
    const CLOSED     = 'FECHADA';
    const OPEN       = 'ABERTA';

    //=======================

    public function isOpen(){
        return $this->status == CreditCardInvoice::OPEN;
    }

    public function isClosed(){
        return $this->status == CreditCardInvoice::CLOSED;
    }

    public function bankAccount(){
        return $this->belongsTo(\Models\BankAccount::class, 'contaBancaria', 'id');
    }

    public function creditCard(){
        return $this->belongsTo(\Models\CreditCard::class, 'cartaoCredito', 'id');
    }

    public function movements(){
        return $this->belongsToMany(\Models\Movement::class, 'movimentosFatura', 'fatura', 'movimento')
                                  ->with(['bankAccount','creditCard','finality','invoice']);
    }

    //======================

    public static function pay(){
        global $logger;
        $logger->addInfo('CreditCardInvoice:Paying invoice: ' . $this->creditCard->descricao . ' ' . $this->mesReferencia);
        
        //generate movement payment
        $movement = new Movement();
        $movement->descricao = 'FATURA (' . $this->creditCard->descricao . ' ' . strtoupper($this->mesReferencia) . ')';
        $movement->emissao = $this->emissao;
        $movement->vencimento = $this->vencimento;
        $movement->valor = $this->valorPagamento;
        $movement->status = Movement::STATUS_PAYD;
        $movement->operacao = Movement::DEBIT;
        $movement->finality = new Finality(['id'=>549]);
        $movement->bankAccount = $this->contaBancaria;
        $movement->paymentForm = $this->formaPagamento;
        $movement->usuario = $this->usuario;
        $movement->fatura = $this;

        $logger->addInfo('CreditCardInvoice:adding movement payment...');  
        $movement->save();

        //atualiza movimentos da fatura para PAGO 
        $logger->addInfo('CreditCardInvoice:updating movements invoice to payd...');  

        $this->movements->update(['status'=>Movement::STATUS_PAYD]);

        $this->status = CreditCardInvoice::CLOSED;
        $this->save();   
    }

    public static function unpay(){
        global $logger;
        $logger->addInfo('CreditCardInvoice:Undone payment invoice: ' . $this->creditCard->descricao . ' ' . $this->mesReferencia);        
        
        $logger->addInfo('CreditCardInvoice: deleting movement of payment invoice where invoice equal ' . $this->id);
        Movement::where('fatura', $this->id)->destroy();
        
        //atualiza movimentos para ABERTO    
        $logger->addInfo('CreditCardInvoice:updating movements invoice to open...');  
        $this->movements->update(['status'=>Movement::STATUS_TO_PAY]); 

        $logger->addInfo('CreditCardInvoice:set invoice open...');  
        $this->status = CreditCardInvoice::OPEN;
        $this->save();
    }

}

?>