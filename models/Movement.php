<?php

namespace Models;  
use \Models\CreditCardInvoice as CreditCardInvoice;
use \App\Util\DateUtil as DateUtil;

class Movement extends \Illuminate\Database\Eloquent\Model {  

    protected $table    = 'movimento';
    const STATUS_PAYD   = 'PAGO';
    const STATUS_TO_PAY = 'A PAGAR';
    const DEBIT         = 'DEBITO';
    const CREDIT        = 'CREDITO';

    public function bankAccount(){
        return $this->belongsTo(\Models\BankAccount::class, 'contaBancaria', 'id');
    }

    public function creditCard(){
        return $this->belongsTo(\Models\CreditCard::class, 'cartaoCredito', 'id');
    }

    public function finality(){
        return $this->belongsTo(\Models\Finality::class, 'finalidade', 'id');
    }

    public function invoice(){
        return $this->belongsTo(\Models\CreditCardInvoice::class, 'fatura', 'id');
    }

   
    public function validateUpdateDelete(){

        if ( $this->invoice != null && $this->invoice->id > 0 ){
            $logger->addInfo('\n Movement validate update/delete: isInvoicePayment.' );
            throw new Exception("O movimento não pode ser alterado/removido pois se trata do pagamento de fatura de cartão de crédito. Caso deseje, cancele o pagamento da fatura para que o movimento seja removido.");
        }

        if ( !empty($this->hashTransferencia) ){
            $logger->addInfo('\n Movement validate update/delete: movimento é uma transfência bancária.' );
            throw new Exception("O movimento é uma transferência bancária e não pode ser alterado/removido. Tente extornar o lançamento.");
        }

        $movementOld = Movement::find($this->id);
        $movementInvoice = MovementsInvoice::where('movimento', $movementOld->id)->first();

        if ( $this->isInClosedInvoice() ){
            if ( ($movementOld->creditCard->id != $this->creditCard->id) ||
                 ($movementOld->operacao != $this->operacao) ||
                 ($movementOld->vencimento != $this->vencimento) ){
    
                $logger->addInfo('Movement validate update/delete: tentativa de alterar o cc/vencimento/operacao de um movimento em fatura fechada.' );
                throw new Exception('O movimento selecionado está relacionado a uma fatura FECHADA. É necessário primeiro reabrir a fatura para alterar/remover o movimento.');
            }
        }
    }

    public function isInClosedInvoice(){
        $movementInvoice = MovementsInvoice::where('movimento', $this->id)->first();

        //o movimento está em uma fatura
        if ( $movementInvoice != null ){ //is in invoice
            $invoice = CreditCardInvoice::find($movementInvoice->fatura);
            if ( $invoice != null && $invoice->isClosed() ){
                return true;
            }
        }

        return false;
    }

    public function isInOpenInvoice(){
        $movementInvoice = MovementsInvoice::where('movimento', $this->id)->first();

        //o movimento está em uma fatura
        if ( $movementInvoice != null ){ //is in invoice
            $invoice = CreditCardInvoice::find($movementInvoice->fatura);
            if ( $invoice != null && !$invoice->isClosed() ){
                return true;
            }
        }

        return false;
    }

    public function addToInvoice(){
        $mesReferencia = DateUtil::mesReferenciaFromDateString($this->vencimento);
        $invoice = CreditCardInvoice::whereRaw('cartaoCredito = ? and mesReferencia = ?', [$this->creditCard->id, $mesReferencia])->first();

        if ( $invoice == null ){
            $emissao = date_format(now(), 'Ymd');
            if ( DateUtil::getMonth($this->vencimento) == '01' ){
                $ano = (intval(DateUtil::getYear($this->vencimento)) + 1);
                $mes = DateUtil::getMonth($this->vencimento);
                $dia = $this->creditCard->dataFechamento;
                $emissao = $ano.$mes.$dia;
            }else{ 
                $ano = DateUtil::getYear($this->vencimento);
                $mes = DateUtil::getMesProximo(DateUtil::getMonth($this->vencimento));
                $dia = $this->creditCard->dataFechamento;
                $emissao = $ano.$mes.$dia;
            }

            $invoice = new CreditCardInvoice();
            $invoice->emissao       = $emissao;
            $invoice->vencimento    = $this->vencimento;
            $invoice->mesReferencia = $mesReferencia;
            $invoice->creditCard    = $this->creditCard;
            $invoice->usuario       = $this->usuario;

            $invoice->save();
        }

        $movementInvoice = new MovementsInvoice();
        $movementInvoice->fatura = $invoice->id;
        $movementInvoice->movimento = $this->id;
        $movementInvoice->save();
    }

}

?>