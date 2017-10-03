<?php

require_once("dao/DB.php");
require_once("dao/SQLs.php");
require_once("models/CreditCardInvoice.php");

class Movement{

    const TABLE_NAME='movimento';

    public static function listByUserPeriod($user, $period){
        $sql = SQL_MOVIMENTO . " WHERE m.usuario = :user 
                                 and SUBSTRING(m.vencimento, 1, 6) = :period 
                                 and m.hashTransferencia = ''
                                 order by m.vencimento desc, m.emissao desc, m.descricao asc";
        $result = DB::executeQuery($sql, [':user' => $user, ':period' => $period]);
        return Movement::resultToArray($result);
    }

    public static function findByID($id){
        $sql = SQL_MOVIMENTO . " WHERE m.id = :id ";
        $result = DB::executeQuery($sql, [':id' => $id]);
        return Movement::resultToArray($result);
    }

    public function listByInvoice($invoice){
        $sql = SQL_MOVIMENTO . " inner join movimentosFatura mf on mf.movimento = m.id 
                                 where mf.fatura = :invoice
                                 order by m.vencimento desc, m.emissao desc, m.descricao asc";
        $result = DB::executeQuery($sql, [':invoice' => $invoice]);
        return Movement::resultToArray($result);
    }

    public function listByBankAccountPeriod($bankAccount, $period){
        $sql = SQL_MOVIMENTO . " WHERE m.contaBancaria = :bankAccount 
                                 and SUBSTRING(m.vencimento, 1, 6) = :period 
                                 order by m.vencimento desc, m.emissao desc, m.descricao asc"
        $result = DB::executeQuery($sql, [':bankAccount' => $bankAccount, 'period' => $period]);
        return Movement::resultToArray($result);
    }

    public function getPreviousBalance($user, $period){
        $result = DB::executeQuery(PREVIOUS_BALANCE, [':user' => $user, 'period' => $period]);
        $balance = 0;
        foreach ($result as $key => $row) {
            strtoupper($row['operacao']) == 'DEBITO' ? $balance -= $row['valor'] : $balance += $row['valor'];
        }
        return $balance; 
    }

    public function getPreviousBalanceBankAccount($bankAccount, $period){
        $result = DB::executeQuery(PREVIOUS_BALANCE_BANK_ACCOUNT, [':bankAccount' => $bankAccount, 'period' => $period]);
        $balance = 0;
        foreach ($result as $key => $row) {
            strtoupper($row['operacao']) == 'DEBITO' ? $balance -= $row['valor'] : $balance += $row['valor'];
        }
        return $balance; 
    }

    public static function insert($vo){
        $id = DB::insert(Movement::TABLE_NAME, 
            ['descricao','emissao','vencimento','valor','status','operacao','finalidade',
             'contaBancaria','fornecedor','cartaoCredito','formaPagamento','usuario'], 
             [$vo->descricao,$vo->emissao,$vo->vencimento,$vo->valor,$vo->status,$vo->operacao,$vo->finalidade->id,
              $vo->contaBancaria->id,$vo->fornecedor->id,$vo->cartaoCredito->id,$vo->formaPagamento->id,$vo->usuario]);

        echo 'Movement ID: ' . $id;

        //se o movimento for de cartão de crédito gerenciar a fatura
        if ( $vo->cartaoCredito->id > 0 ) ){
            echo 'CREDIT CARD ID: ' . $vo->cartaoCredito->id;
            CreditCardInvoice::addToInvoice($vo);
        }
        return $id;
    }

    public static function update($vo){

        if ( Movement::isInvoicePayment($vo) ){
            throw new Exception("O movimento não pode ser alterado pois se trata do pagamento de fatura de cartão de crédito. Caso deseje, cancele o pagamento da fatura para que o movimento seja removido.");
        }

        if ( Movement::isInClosedInvoice($vo) ){
            throw new Exception('O movimento selecionado está relacionado a uma fatura FECHADA. É necessário primeiro reabrir a fatura para alterar o movimento.');
        }

        $old_vo = Movement::findByID($vo->id);

        //se o movimento está associado a um cartão de credito
        if ( !empty($vo->cartaoCredito) && $vo->cartaoCredito->id > 0 ){
            if ( !empty($old_vo->cartaoCredito) && $old_vo->cartaoCredito->id > 0 ){
                //se houve alteração do cartão de crédito ou data de vencimento - remove da fatura
                if ( $vo->cartaoCredito->id != $old_vo->cartaoCredito->id || $vo->vencimento != $old_vo->vencimento ){
                    Movement::removeFromInvoice($fatura->id, $vo->id);
                }
            }
            //adiciona na nova fatura
            CreditCardInvoice::addToInvoice($vo);
        }

        return DB::update(Movement::TABLE_NAME, 
            ['descricao' => $vo->descricao,'emissao' => $vo->emissao,'vencimento' => $vo->vencimento,
             'valor' => $vo->valor,'status' => $vo->status,'operacao' => $vo->operacao,
             'finalidade' => $vo->finalidade->id,'contaBancaria' => $vo->contaBancaria->id,'fornecedor' => $vo->fornecedor->id,
             'cartaoCredito' => $vo->cartaoCredito->id,'formaPagamento' => $vo->formaPagamento->id], $vo->id);
    }

    public static function delete($id){
        $old_vo = Movement::findByID($vo->id);

        //se o movimento está ligado a uma fatura
        if ( !empty($old_vo->fatura) && $old_vo->fatura->id > 0 ){
            $fatura = CreditCardInvoice::findByID($old_vo->fatura->id);
            if ( $fatura->status == CreditCardInvoice::STATUS_CLOSED ){
                throw new Exception('A fatura do cartão para o período selecionado já está fechada. 
                É necessário cancelar o pagamento para reabrir a fatura e poder fazer novos lançamentos.');                      
            }else{
               Movement::removeFromInvoice($fatura->id, $id);
            }
        }
        return DB::delete(Movement::TABLE_NAME, );
    }

    //===========INVOICES=========================
    public static function removeFromInvoice($invoice_id, $movement_id){
        //remove o movimento da fatura que ele estiver ligado    
        DB::PDO()->delete()->from("movimentosFatura")->where('fatura', '=', $invoice_id, 'and', 'movimento', '=', $movement_id);
    }

    private static function isInClosedInvoice($movement){
        $result DB::executeQuery(MOVEMENT_CLOSED_INVOICE, [':movement' => $movement->id]);
        //O MOVIMENTO ESTÁ ASSOCIADO A UMA FATURA FECHADA
        if ( $result->status == CreditCardInvoice::STATUS_CLOSED ){
            return true;
        }
        return false;
    }

    private static function isInvoicePayment($movement){
        //O MOVIMENTO É O PAGAMENTO DE UMA FATURA  
        if ( !empty($movement->fatura) && $movement->fatura->id > 0 ){
            return true;
        }
        return false;
    }

    //===============CONVERTE RETORNO DO BANCO EM LISTA DE OBJTOS======================
    public function resultToArray($result){
        $list = array();
        
        foreach ($result as $key => $value) {
            array_push($list, $this->rowToObject($value));
        }

        return $list;
    }
    
    public function rowToObject($row){
        $movimento                     = new stdClass();
        $movimento->id                 = $row['id'];
        $movimento->descricao          = $row['descricao'];
        $movimento->idUsuario          = $row['usuario'];
        $movimento->emissao            = $row['emissao']; 
        $movimento->vencimento         = $row['vencimento']; 
        $movimento->movimentoOrigem    = $row['movimentoOrigem'];
        $movimento->parcela            = $row['parcela'];
        $movimento->valor              = $row['valor'];
        $movimento->hashParcelas       = $row['hashParcelas'];
        $movimento->hashTransferencia  = $row['hashTransferencia'];
        $movimento->status             = $row['status'];
        $movimento->operacao           = $row['operacao'];
        
        if ( $row['idFinalidade'] > 0 ){
            $finalidade                    = new stdClass();
            $finalidade->id                = $row['idFinalidade'];
            $finalidade->descricao         = $row['descricaoFinalidade'];
            $finalidade->idUsuario         = $row['usuario'];
            $movimento->finalidade         = $finalidade;
        }

        if ( $row['idContaBancaria'] > 0 ){
            $contaBancaria                 = new stdClass();
            $contaBancaria->id             = $row['idContaBancaria'];
            $contaBancaria->descricao      = $row['descricaoContaBancaria'];
            $contaBancaria->numero         = $row['numeroContaBancaria'];
            $contaBancaria->digito         = $row['digitoContaBancaria'];
            $contaBancaria->usuario        = $row['usuario'];
            $movimento->contaBancaria      = $contaBancaria;
        }        

        if ( $row['idFornecedor'] > 0 ){
            $fornecedor                    = new stdClass();
            $fornecedor->id                = $row['idFornecedor'];
            $fornecedor->descricao         = $row['descricaoFornecedor'];
            $movimento->fornecedor         = $fornecedor;
        }
        
        if ( $row['idFormaPagamento'] > 0 ){
            $formaPagamento                = new stdClass();
            $formaPagamento->id            = $row['idFormaPagamento'];
            $formaPagamento->descricao     = $row['descricaoFormaPagamento'];
            $formaPagamento->sigla         = $row['siglaFormaPagamento'];
            $formaPagamento->usuario       = $row['usuario'];
            $movimento->formaPagamento     = $formaPagamento;
        }

        if ( $row['idCartaoCredito'] > 0 ){
            $cartaoCredito                 = new stdClass();
            $cartaoCredito->id             = $row['idCartaoCredito'];
            $cartaoCredito->descricao      = $row['descricaoCartaoCredito'];
            $cartaoCredito->limite         = $row['limite'];
            $cartaoCredito->dataFatura     = $row['dataFatura'];
            $cartaoCredito->dataFechamento = $row['dataFechamento'];
            $cartaoCredito->usuario        = $row['usuario'];
            $movimento->cartaoCredito      = $cartaoCredito;
        }
        
        if ( $row['idFatura'] > 0 ){
            $fatura                        = new stdClass();
            $fatura->id                    = $row['idFatura'];
            $fatura->mesReferencia         = $row['mesReferencia'];
            $fatura->valor                 = $row['valorFatura'];
            $fatura->valorPagamento        = $row['valorPagamentoFatura'];
            $fatura->usuario               = $row['usuario'];
            $movimento->fatura             = $fatura;
        }

        return $movimento;
    }

}

?>