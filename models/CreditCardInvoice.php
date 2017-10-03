<?php

require_once("dao/DB.php");
require_once("dao/SQLs.php");
require_once("models/CreditCard.php");
require_once("app/util/DateUtil.php");

class CreditCardInvoice{

    const TABLE_NAME    = 'fatura';
    const STATUS_CLOSED = 'FECHADA';

    public static function listByCreditCard($creditCard){
        $result = DB::executeQuery(SQL_FATURA, [':creditCard' => $creditCard]);
        return CreditCardInvoice::resultToArray($result);
    }

    public static function findByID($id){
        $result = DB::findByID(TABLE_NAME, [':id' => $id]);
        return CreditCardInvoice::resultToArray($result);
    }


    public static function findByCrediCardPeriod($creditCard, $period){
        $result = DB::executeQuery(INVOICE_BY_PERIOD_REFERENCE, [':creditCard' => $creditCard, ':period' => $period]);
        return CreditCardInvoice::resultToArray($result);
    }

    public static function insert($vo){
        return DB::insert(CreditCardInvoice::TABLE_NAME, 
            ['descricao', 'emissao', 'vencimento', 'cartaoCredito', 'mesReferencia', 'usuario'], 
            [$vo->descricao, $vo->emissao, $vo->vencimento, $vo->cartaoCredito, $vo->mesReferencia, $vo->usuario]);
    }

    //ao salvar um movimento pago com cartao de credito, adiciona-o na fatura atual
    public static function addToInvoice($movement){
        echo "ADD TO INVOICE";

        $cc = CreditCard::findByID($movement->cartaoCredito->id);

        $mesReferencia = DateUtil:mesReferenciaFromDateString($movement->vencimento);

        //VERIFICA SE EXISTE FATURA PARA O MES DE REFERENCIA
        $invoice = CreditCardInvoice::findByCrediCardPeriod($movement->cartaoCredito->id, $mesReferencia);
        
        //SE EXISTIR FATURA, INSERE MOVIMENTO
        if ( !empty($invoice) && $invoice->id > 0 ){
            if ( !$invoice->status == STATUS_CLOSED ){
                $invoice_id = $invoice->id;
                DB::insert("movimentosFatura", ['fatura', 'movimento'], [$invoice->id, $movement->id]);
            }else{
                //FATURA JÁ ESTÁ PAGA, DEVE SER REABERTA PARA ADICIONAR MOVIMENTOS
                throw new Exception('A fatura do cartão para o período selecionado já está fechada. 
                    É necessário cancelar o pagamento para reabrir a fatura e poder fazer novos lançamentos.');
            }
        }else{
            //CADASTRA NOVA FATURA PARA INSERIR O MOVIMENTO
            if ( $mes == '01' ){
                $emissao = (intval($year) + 1) . $mes . $cc->dataFechamento;
            }else{ 
                $emissao = $year . DateUtil::getMesProximo($mes) . $cc->dataFechamento;
            }

            $invoice = new stdClass();
            $invoice->emissao = $emissao;
            $invoice->vencimento = $movement->vencimento;//pega o vencimento calculado pelo front end
            $invoice->mesReferencia = $mesReferencia.'/'.$year;
            $invoice->usuario = $movement->usuario;
            $invoice->cartaoCredito = $movement->cartaoCredito;

            $invoice_id = CreditCardInvoice::insert($invoice);
            //INSERE O MOVIMENTO NA FATURA
            DB::insert("movimentosFatura", ['fatura', 'movimento'], [$invoice_id, $movement->id]);
        }
        return $invoice_id;
    }

    public static function resultToArray($result){
        $list = array();
    
        foreach ($result as $key => $value) {
            array_push($list, CreditCardInvoice::rowToObject($value));
        }
        return $list;
    }
        
    public static function rowToObject($row){
        $fatura                        = new stdClass();
        $fatura->id                    = $row['id'];
        $fatura->emissao               = $row['emissao'];
        $fatura->idUsuario             = $row['usuario'];
        $fatura->vencimento            = $row['vencimento'];
        $fatura->valor                 = $row['valor'];
        $fatura->valorPagamento        = $row['valorPagamento'];
        $fatura->mesReferencia         = $row['mesReferencia'];
        $fatura->status                = $row['status'];
        
        if ( $row['idCartaoCredito'] > 0 ){
            $cartaoCredito                 = new stdClass();
            $cartaoCredito->id             = $row['idCartaoCredito'];
            $cartaoCredito->descricao      = $row['descricaoCartaoCredito'];
            $cartaoCredito->limite         = $row['limite'];
            $cartaoCredito->dataFatura     = $row['dataFatura'];
            $cartaoCredito->dataFechamento = $row['dataFechamento'];
            $cartaoCredito->idUsuario      = $row['usuario'];
            $fatura->cartaoCredito         = $cartaoCredito;
        }

        if ( $row['idFormaPagamento'] > 0 ){
            $formaPagamento                = new stdClass();
            $formaPagamento->id            = $row['idFormaPagamento'];
            $formaPagamento->descricao     = $row['descricaoFormaPagamento'];
            $formaPagamento->sigla         = $row['siglaFormaPagamento'];
            $formaPagamento->idUsuario     = $row['usuario'];
            $fatura->formaPagamento        = $formaPagamento;
        }

        if ( $row['idContaBancaria'] > 0 ){
            $contaBancaria                 = new stdClass();
            $contaBancaria->id             = $row['idContaBancaria'];
            $contaBancaria->descricao      = $row['descricaoContaBancaria'];
            $contaBancaria->numero         = $row['numeroContaBancaria'];
            $contaBancaria->idUsuario      = $row['usuario'];
            $fatura->contaBancaria         = $contaBancaria;
        }
        return $fatura;
    }

}

?>