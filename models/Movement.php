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
        $sql = SQL_MOVIMENTO . " WHERE m.id = :id";
        $result = DB::fetchUnique($sql, [':id' => $id]);
        return Movement::rowToObject($result);
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
                                 order by m.vencimento desc, m.emissao desc, m.descricao asc";
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

        $vo->id = $id;

        //se o movimento for de cartão de crédito gerenciar a fatura
        if ( isset($vo->cartaoCredito) && $vo->cartaoCredito->id > 0 ){
            echo 'CREDIT CARD ID: ' . $vo->cartaoCredito->id;
            CreditCardInvoice::addToInvoice($vo);
        }
        
        return $vo;
    }

    public static function update($vo){

        global $logger;

        if ( Movement::isInvoicePayment($vo) ){
            $logger->addInfo('\n Movement Update: isInvoicePayment.' );
            throw new Exception("O movimento não pode ser alterado pois se trata do pagamento de fatura de cartão de crédito. Caso deseje, cancele o pagamento da fatura para que o movimento seja removido.");
        }

        if ( !empty($vo->hashTransferencia) ){
            $logger->addInfo('\n Movement:update: movimento é uma transfência bancária.' );
            throw new Exception("O movimento é uma transferência bancária e não pode ser alterado. Estorne a transfência e relance novamente.");
        }

        $old_vo = Movement::findByID($vo->id);

        if ( Movement::isInClosedInvoice($vo) ){
            $logger->addInfo('Movement Update: isInClosedInvoice.' );
            //se não alterou/removeu o cartão de credito, nem data de vencimento e operacao não tem problema
            if ( isset($vo->cartaoCredito) && $vo->cartaoCredito->id <= 0 
                    || ($vo->cartaoCredito->id != $old_vo->cartaoCredito->id || $vo->vencimento != $old_vo->vencimento || $vo->operacao != $old_$vo->operacao) ){
                    $logger->addInfo('Movement Update: tentativa de alterar o cc/vencimento/operacao de um movimento em fatura fechada.' );
                    throw new Exception('O movimento selecionado está relacionado a uma fatura FECHADA. É necessário primeiro reabrir a fatura para alterar o movimento.');
            }
        }else{

            //se o movimento está associado a um cartão de credito
            if ( isset($vo->cartaoCredito) && $vo->cartaoCredito->id > 0 ){
                $logger->addInfo('Movement Update: movimento associado a um cartão de crédito.' );
                if ( isset($old_vo->cartaoCredito) && $old_vo->cartaoCredito->id > 0 ){
                    //se houve alteração do cartão de crédito ou data de vencimento - remove da fatura
                    if ( $vo->cartaoCredito->id != $old_vo->cartaoCredito->id || $vo->vencimento != $old_vo->vencimento ){
                        $logger->addInfo('Movement Update: removendo movimento da fatura que ele estava associado.' );
                        Movement::removeFromInvoice($fatura->id, $vo->id);
                        CreditCardInvoice::addToInvoice($vo);
                    }
                }else{
                    //adiciona na nova fatura
                    CreditCardInvoice::addToInvoice($vo);
                }

            }

        }
        
        $logger->addInfo('Movement Update: preparando para alterar movimento...' );

        DB::update(Movement::TABLE_NAME, 
            ['descricao' => $vo->descricao,'emissao' => $vo->emissao,'vencimento' => $vo->vencimento,
             'valor' => $vo->valor,'status' => $vo->status,'operacao' => $vo->operacao,
             'finalidade' => $vo->finalidade->id,
             'contaBancaria' => isset($vo->contaBancaria) ? $vo->contaBancaria->id : null,
             'fornecedor' => isset($vo->fornecedor) ? $vo->fornecedor->id : null,
             'cartaoCredito' => isset($vo->cartaoCredito) ? $vo->cartaoCredito->id : null,
             'formaPagamento' => isset($vo->formaPagamento) ? $vo->formaPagamento->id : null], $vo->id);

        return $vo;
    }

    public static function delete($id){
        global $logger;
        $vo = Movement::findByID($id);

        if ( Movement::isInvoicePayment($vo) ){
            $logger->addInfo('Movement:delete: isInvoicePayment.' );
            throw new Exception("O movimento não pode ser removido pois se trata do pagamento de fatura de cartão de crédito. Caso deseje, cancele o pagamento da fatura para que o movimento seja removido.");
        }

        if ( !empty($vo->hashTransferencia) ){
            $logger->addInfo('Movement:delete: movimento é uma transfência bancária.' );
            throw new Exception("O movimento é uma transferência bancária e não pode ser removido. Utilize a função de estorno.");
        }

        //se o movimento está ligado a uma fatura
        $invoice = Movement::isInInvoice($vo);
        if ( isset($invoice) && $invoice->id > 0 ){
            $logger->addInfo('Movement:delete: movimento está em uma fatura.' );
            if ( $invoice->status == CreditCardInvoice::STATUS_CLOSED ){
                throw new Exception('O movimento pertenece a uma fatura fechada. É necessário cancelar o pagamento da fatura para poder remover o lançamento.');                      
            }else{
                $logger->addInfo('Movement:delete: removendo movimento da fatura.' );
                Movement::removeFromInvoice($invoice->id, $id);
            }
        }
        return DB::delete(Movement::TABLE_NAME, $id);
    }

    //===========INVOICES=========================
    public static function removeFromInvoice($invoice_id, $movement_id){
        //remove o movimento da fatura que ele estiver ligado    
        DB::PDO()->delete()->from("movimentosFatura")->where('fatura', '=', $invoice_id, 'and', 'movimento', '=', $movement_id);
    }

    private static function isInClosedInvoice($movement){
        $result = DB::fetchUnique(MOVEMENT_CLOSED_INVOICE, [':movement' => $movement->id]);
        //O MOVIMENTO ESTÁ ASSOCIADO A UMA FATURA FECHADA
        if ( isset($result) && $result['status'] == CreditCardInvoice::STATUS_CLOSED ){
            return true;
        }
        return false;
    }

    private static function isInInvoice($movement){
        $result = DB::fetchUnique(MOVEMENT_IN_INVOICE, [':movement' => $movement->id]);
        return CreditCardInvoice::rowToObject($result);
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
            array_push($list, Movement::rowToObject($value));
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