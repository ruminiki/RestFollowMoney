<?php

require_once("app/util/DateUtil.php");

namespace Models;

class CreditCardInvoice extends \Illuminate\Database\Eloquent\Model {

    protected $table    = 'fatura';
    const STATUS_CLOSED = 'FECHADA';
    const STATUS_OPEN   = 'ABERTA';

    public static function insert($vo){
        try{
            $data = json_decode($request->getBody(), false);

            $cc = new CreditCardInvoice();
            $cc->emissao        = $data->descricao;
            $cc->vencimento     = $data->limite;
            $cc->dataFatura     = $data->dataFatura;
            $cc->dataFechamento = $data->dataFechamento;
            $cc->usuario        = $data->usuario;

            $cc->save();

            return $in;
        }catch(Exception $e){
            throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
        }
    }

    public static function payInvoice($vo){
        global $logger;
        $logger->addInfo('CreditCardInvoice:PAYING invoice: ' . $vo->cartaoCredito->descricao . ' ' . $vo->mesReferencia);  
        DB::beginTransaction();

        //generate movement payment

        $movement = new stdClass();
        $movement->descricao = 'FATURA (' . $vo->cartaoCredito->descricao . ' ' . strtoupper($vo->mesReferencia) . ')';
        $movement->emissao = $vo->emissao;
        $movement->vencimento = $vo->vencimento;
        $movement->valor = $vo->valorPagamento;
        $movement->status = 'PAGO';
        $movement->operacao = 'DEBITO';
        $finality = new stdClass();
        $finality->id = 549;
        $movement->finalidade = $finality;
        $movement->contaBancaria = $vo->contaBancaria;
        $movement->formaPagamento = $vo->formaPagamento;
        $movement->usuario = $vo->usuario;
        $movement->fatura = $vo;

        $logger->addInfo('CreditCardInvoice:adding movement payment...');  
        Movement::insert($movement);

        //atualiza movimentos para PAGO    
        $sql = "update movimento set status = 'PAGO' 
                where id in (select movimento from movimentosFatura where fatura = :invoice)";
        
        DB::queryUpdate($sql, [':invoice'=>$vo->id]);

        $vo->status = CreditCardInvoice::STATUS_CLOSED;
        DB::update(CreditCardInvoice::TABLE_NAME, 
            ['formaPagamento' => $vo->formaPagamento->id,
             'contaBancaria' => $vo->contaBancaria->id,
             'valorPagamento' => $vo->valorPagamento,
             'status' => $vo->status], $vo->id);

        DB::commit();    
        return $vo;
    }

    public static function unpayInvoice($vo){
        global $logger;
        $logger->addInfo('CreditCardInvoice:UNDONE PAYment invoice: ' . $vo->cartaoCredito->descricao . ' ' . $vo->mesReferencia);        
        DB::beginTransaction();   
        
        $logger->addInfo('CreditCardInvoice: deleting movement of payment invoice where invoice equal ' . $vo->id);
        //delete movement payment
        $sql = "delete from movimento where fatura = :invoice";
        DB::queryDelete($sql, [':invoice'=>$vo->id]);

        //atualiza movimentos para ABERTO    
        $sql = "update movimento set status = 'A VENCER' where id in (select movimento from movimentosFatura where fatura = :invoice)";
        
        DB::queryUpdate($sql, [':invoice'=>$vo->id]);

        $vo->status = CreditCardInvoice::STATUS_OPEN;
        DB::update(CreditCardInvoice::TABLE_NAME, 
            ['formaPagamento' => null,
             'contaBancaria' => null,
             'valorPagamento' => null,
             'status' => $vo->status], $vo->id);

        DB::commit();    
        return $vo;
    }

    //ao salvar um movimento pago com cartao de credito, adiciona-o na fatura atual
    public static function addToInvoice($movement){
        global $logger;
        $logger->addInfo('CreditCardInvoice:addToInvoice: ' . $movement->descricao);
        $cc = CreditCard::findByID($movement->cartaoCredito->id);

        $logger->addInfo('CreditCardInvoice:cartaoCreditoFatura: ' . $cc->descricao);
        $mesReferencia = DateUtil::mesReferenciaFromDateString($movement->vencimento);

        $logger->addInfo('CreditCardInvoice:mesReferencia: ' . $mesReferencia);
        //VERIFICA SE EXISTE FATURA PARA O MES DE REFERENCIA
        $invoice = CreditCardInvoice::findByCrediCardPeriod($movement->cartaoCredito->id, $mesReferencia);
        
        //SE EXISTIR FATURA, INSERE MOVIMENTO
        if ( isset($invoice) && $invoice->id > 0 ){
            $logger->addInfo('CreditCardInvoice:invoice status: ' . $invoice->status);
            if ( !($invoice->status == CreditCardInvoice::STATUS_CLOSED) ){
                $logger->addInfo('CreditCardInvoice:adicionando movimento a fatura...');
                $invoice_id = $invoice->id;
                DB::insert("movimentosFatura", ['fatura', 'movimento'], [$invoice->id, $movement->id]);
                $logger->addInfo('CreditCardInvoice:movimento adicionado');
            }else{
                $logger->addInfo('CreditCardInvoice:fatura paga. Não pode ser feito o lançamento de novos movimentos.');
                //FATURA JÁ ESTÁ PAGA, DEVE SER REABERTA PARA ADICIONAR MOVIMENTOS
                throw new Exception('A fatura do cartão para o período selecionado já está fechada. É necessário cancelar o pagamento para reabrir a fatura e poder fazer novos lançamentos.');
            }
        }else{
            $logger->addInfo('CreditCardInvoice:invoice: não encontrada fatura para '  . $mesReferencia . ' Cadastrando nova.');
            //CADASTRA NOVA FATURA PARA INSERIR O MOVIMENTO
            if ( DateUtil::getMonth($movement->vencimento) == '01' ){
                $emissao = (intval(DateUtil::getYear($movement->vencimento)) + 1) . DateUtil::getMonth($movement->vencimento) . $cc->dataFechamento;
            }else{ 
                $emissao = DateUtil::getYear($movement->vencimento) . DateUtil::getMesProximo(DateUtil::getMonth($movement->vencimento)) . $cc->dataFechamento;
            }

            $invoice = new stdClass();
            $invoice->emissao = $emissao;
            $invoice->vencimento = $movement->vencimento;//pega o vencimento calculado pelo front end
            $invoice->mesReferencia = $mesReferencia;
            $invoice->usuario = $movement->usuario;
            $invoice->cartaoCredito = $movement->cartaoCredito->id;

            $invoice_id = CreditCardInvoice::insert($invoice);

            $logger->addInfo('CreditCardInvoice:nova fatura:' . $movement->cartaoCredito->descricao . ' ' . $mesReferencia);
            //INSERE O MOVIMENTO NA FATURA
            DB::insert("movimentosFatura", ['fatura', 'movimento'], [$invoice_id, $movement->id]);
            $logger->addInfo('CreditCardInvoice:adicionado movimento $movimento->descricao a fatura $invoice_id.');
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
        $fatura->usuario               = $row['usuario'];
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
            $cartaoCredito->usuario      = $row['usuario'];
            $fatura->cartaoCredito         = $cartaoCredito;
        }

        if ( $row['idFormaPagamento'] > 0 ){
            $formaPagamento                = new stdClass();
            $formaPagamento->id            = $row['idFormaPagamento'];
            $formaPagamento->descricao     = $row['descricaoFormaPagamento'];
            $formaPagamento->sigla         = $row['siglaFormaPagamento'];
            $formaPagamento->usuario     = $row['usuario'];
            $fatura->formaPagamento        = $formaPagamento;
        }

        if ( $row['idContaBancaria'] > 0 ){
            $contaBancaria                 = new stdClass();
            $contaBancaria->id             = $row['idContaBancaria'];
            $contaBancaria->descricao      = $row['descricaoContaBancaria'];
            $contaBancaria->numero         = $row['numeroContaBancaria'];
            $contaBancaria->usuario      = $row['usuario'];
            $fatura->contaBancaria         = $contaBancaria;
        }
        return $fatura;
    }

}

?>