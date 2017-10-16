<?php

namespace Models;  
use \Models\CreditCardInvoice as CreditCardInvoice;
use \App\Util\DateUtil as DateUtil;
use Exception;

class Movement extends \Illuminate\Database\Eloquent\Model {  

    protected $table    = 'movimento';
    //const UPDATED_AT    = "movimento.updated_at";

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

    public function movementsInvoice(){
        return $this->belongsToMany(\Models\Movement::class, 'movimentosFatura', 'fatura', 'movimento')->withTimestamps();
    }

    public function validateUpdateDelete($operation="U"){

        global $logger;
        $logger->addInfo('Validate update/delete movement...');

        if ( $this->invoice != null && $this->invoice->id > 0 ){
            $logger->addInfo('\n Movement validate update/delete: isInvoicePayment.' );
            throw new Exception("O movimento não pode ser alterado/removido pois se trata do pagamento de fatura de cartão de crédito. Caso deseje, cancele o pagamento da fatura para que o movimento seja removido.");
        }

        if ( $operation == "U" ){
            if ( !empty($this->hashTransferencia) ){
                $logger->addInfo('\n Movement validate update/delete: movimento é uma transfência bancária.' );
                throw new Exception("O movimento é uma transferência bancária e não pode ser alterado. Tente extornar o lançamento.");
            }
        }

        $movementOld = Movement::find($this->id);
        $movementInvoice = MovementsInvoice::where('movimento', $movementOld->id)->first();

        if ( $this->isInClosedInvoice() ){
            if ( $operation == "D" ){
                $logger->addInfo('Movement validate: tentativa de remover um movimento em fatura fechada.' );
                throw new Exception('O movimento selecionado está relacionado a uma fatura FECHADA. É necessário primeiro reabrir a fatura para poder remover o movimento.');
            }
            if ( ($movementOld->creditCard->id != $this->creditCard->id) ||
                 ($movementOld->operacao != $this->operacao) ||
                 ($movementOld->status != $this->status) ||
                 ($movementOld->vencimento != $this->vencimento) ){
    
                $logger->addInfo('Movement validate: tentativa de alterar o cc/vencimento/status/operacao de um movimento em fatura fechada.' );
                throw new Exception('O movimento selecionado está relacionado a uma fatura FECHADA. É necessário primeiro reabrir a fatura para alterar o movimento.');
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
        global $logger;

        $mesReferencia = DateUtil::mesReferenciaFromDateString($this->vencimento);
        $invoice = CreditCardInvoice::whereRaw('cartaoCredito = ? and mesReferencia = ?', [$this->cartaoCredito, $mesReferencia])->first();

        if ( $invoice == null ){
            $emissao = date('Ymd');
            if ( DateUtil::getMonth($this->vencimento) == '01' ){
                $ano = (intval(DateUtil::getYear($this->vencimento)) + 1);
                $mes = DateUtil::getMonth($this->vencimento);
                $dia = CreditCard::find($this->cartaoCredito)->dataFechamento;
                $emissao = $ano.$mes.$dia;
            }else{ 
                $ano = DateUtil::getYear($this->vencimento);
                $mes = DateUtil::getMesProximo(DateUtil::getMonth($this->vencimento));
                $dia = CreditCard::find($this->cartaoCredito)->dataFechamento;
                $emissao = $ano.$mes.$dia;
            }

            $invoice = new CreditCardInvoice();
            $invoice->emissao       = $emissao;
            $invoice->vencimento    = $this->vencimento;
            $invoice->mesReferencia = $mesReferencia;
            $invoice->cartaoCredito = $this->cartaoCredito;
            $invoice->usuario       = $this->usuario;

            $logger->addInfo('Movement:adding new invoice: ' . $this->creditCard->descricao . ' ' . $mesReferencia);
            $invoice->save();
        }else{
            if ( $invoice->isClosed() ){
                throw new Exception('A fatura para o período ' . $mesReferencia . ' já está fechada. Para fazer novo lançamento no período é necessário reabri-lá.');
            }
        }

        $movementInvoice = new MovementsInvoice();
        $movementInvoice->fatura    = $invoice->id;
        $movementInvoice->movimento = $this->id;

        $logger->addInfo('Movement:adding movement to invoice: Movement: ' . $this->id . ' Invoice: ' . $invoice->id);
        $this->movementsInvoice()->save($movementInvoice);
                
    }

    public function prepareTransfer($accountTransfer, $codeTransfer, $operation){
        $this->emissao           = $accountTransfer->data;
        $this->vencimento        = $accountTransfer->data;
        $this->finalidade        = $accountTransfer->finalidade->id;
        $this->valor             = $accountTransfer->valor;
        if ( $operation == Movement::CREDIT ){
            $this->operacao          = Movement::CREDIT;
            $this->contaBancaria     = $accountTransfer->contaBancariaDestino->id;
            $this->descricao         = 'TRANSFERÊNCIA DE: ' . $accountTransfer->contaBancariaOrigem->descricao;    
        }else{
            $this->operacao          = Movement::DEBIT;
            $this->contaBancaria     = $accountTransfer->contaBancariaOrigem->id;
            $this->descricao         = 'TRANSFERÊNCIA PARA: ' . $accountTransfer->contaBancariaDestino->descricao;    
        }
        $this->status            = Movement::STATUS_PAYD;
        $this->hashTransferencia = $codeTransfer;
        $this->usuario           = $accountTransfer->usuario;
    }

}

?>