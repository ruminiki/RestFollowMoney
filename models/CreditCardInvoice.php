<?php

require_once("dao/DB.php");
require_once("dao/SQLs.php");

class CreditCardInvoice{

    const TABLE_NAME='fatura';

    public static function listByCreditCard($creditCard){
        $result = DB::executeQuery(SQL_FATURA, [':creditCard' => $creditCard]);
        return CreditCardInvoice::resultToArray($result);
    }

    public static function insert($vo){
        return DB::insertNew(CreditCardInvoice::TABLE_NAME, 
            ['descricao', 'emissao', 'vencimento', 'cartaoCredito', 'mesReferencia', 'usuario'], 
            [$vo->descricao, $vo->emissao, $vo->vencimento, $vo->cartaoCredito, $vo->mesReferencia, $vo->usuario]);
    }

    //ao salvar um movimento pago com cartao de credito, adiciona-o na fatura atual
    public static function addToInvoice($moviment_id, $maturity, $credit_card_id, $user){
        global $app;

        echo "ADD TO INVOICE";

        //CARREGA OS DADOS DO CARTAO DE CREDITO
        $sth = $this->PDO->prepare("SELECT id, dataFatura, dataFechamento FROM cartaoCredito WHERE id = :id");
        $sth ->bindValue(':id',$credit_card_id);
        $sth->execute();
        $credit_card = $sth->fetch(\PDO::FETCH_ASSOC);

        //FORMATA O MES DE REFERENCIA PARA BUSCA DA FATURA
        $mes  = substr($maturity,4,2);
        $year = substr($maturity,0,4);
        
        $mesReferencia = DateUtil::getRepresentacaoMesString($mes) . '/' . $year;
        
        //VERIFICA SE EXISTE FATURA PARA O MES DE REFERENCIA
        $sth = $this->PDO->prepare("SELECT id, status FROM fatura WHERE mesReferencia = :mesReferencia AND cartaoCredito = :cartaoCredito");
        $sth ->bindValue(':cartaoCredito', $credit_card_id);
        $sth ->bindValue(':mesReferencia', $mesReferencia);
        $sth->execute();
        $invoice = $sth->fetch(\PDO::FETCH_ASSOC);
        
        //SE EXISTIR FATURA, INSERE MOVIMENTO
        if ( !empty($invoice) && $invoice['id'] > 0 ){
            if ( $invoice['status'] == 'FECHADA' ){
                //FATURA JÁ ESTÁ PAGA, DEVE SER REABERTA PARA ADICIONAR MOVIMENTOS
                throw new Exception('A fatura do cartão para o período selecionado já está fechada. É necessário cancelar o pagamento para reabrir a fatura e poder fazer novos lançamentos.');
            }else{
                //INSERE MOVIMENTO NA FATURA EXISTENTE
                $sql = "insert into movimentosFatura (fatura, movimento) values ( " . $invoice['id'] . ", $moviment_id)";
                $sth = $this->PDO->prepare($sql);
                $sth->execute();
            }
        }else{
            //CADASTRA NOVA FATURA PARA INSERIR O MOVIMENTO
            $dataFechamento = $credit_card['dataFechamento'];
            

            if ( $mes == '01' ){
                $emissao = (intval($year) + 1) . $mes . $dataFechamento;                
            }else{ 
                $emissao = $year . DateUtil::getMesProximo($mes) . $dataFechamento;             
            }

            //INSERE A FATURA NOVA
            $sql = "insert into fatura ( emissao, vencimento, mesReferencia, usuario, cartaoCredito ) " .
                    "values (" .
                    "'" . $emissao . "'," .
                    "'" . $maturity . "'," .
                    "'$mesReferencia/$year'," . 
                    "$user," . 
                    "$credit_card_id)";

            $sth = $this->PDO->prepare($sql);
            $sth->execute();
            $invoice_id = $this->PDO->lastInsertId();

            //INSERE O MOVIMENTO NA FATURA
            $sql = "insert into movimentosFatura (fatura, movimento) values ( " . $invoice_id . ", $moviment_id)";
            $sth = $this->PDO->prepare($sql);
            $sth->execute();
        }   
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