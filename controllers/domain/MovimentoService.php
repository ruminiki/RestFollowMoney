<?php

include_once("com/followMoney/dao/MovimentoDao.php");
include_once("com/followMoney/domain/valueObjects/MovimentoVO.php");

class MovimentoService
{
    public function save($movimento){
    	return MovimentoDao::save($movimento);
    }
    
    public function update($movimento){
    	return MovimentoDao::update($movimento);
    }
    
    public function remove($movimento){
    	return MovimentoDao::remove($movimento);
    }
    
    public function findAll($usuario){
    	return MovimentoDao::findAll($usuario);
    }
    
    public function listByRange($dtInicial,$dtFinal,$usuario){
    	return MovimentoDao::listByRange($dtInicial,$dtFinal, $usuario);
	}

    public function findByFornecedor($fornecedor, $usuario){
    	return MovimentoDao::findByFornecedor($fornecedor, $usuario);
    }
	
	public function findByDescricao($descricao, $dtInicial, $dtFinal, $usuario){
    	return MovimentoDao::findByDescricao($descricao, $dtInicial, $dtFinal, $usuario);
    }
	
	public function findByDescricaoFinalidade($descricao, $dtInicial, $dtFinal, $usuario){
    	return MovimentoDao::findByDescricaoFinalidade($descricao, $dtInicial, $dtFinal,$usuario);
    }
	
	public function findByDescricaoContaBancaria($descricao, $dtInicial, $dtFinal, $usuario){
    	return MovimentoDao::findByDescricaoContaBancaria($descricao, $dtInicial, $dtFinal,$usuario);
    }

    public function calculaSaldoAnterior($data, $usuario){
        return MovimentoDao::calculaSaldoAnterior($data, $usuario);
    }
    
    public function resumeByYear($year, $usuario){
    	return MovimentoDao::resumeByYear($year, $usuario);
    }
    
    public function findMovimentosProgramados($usuario){
    	return MovimentoDao::findMovimentosProgramados($usuario);
    }
	
	public function findMovimentosByContaBancariaAndRange($contaBancaria, $dataInicial, $dataFinal, $usuario){
    	return MovimentoDao::findMovimentosByCBAndRange($contaBancaria, $dataInicial, $dataFinal, $usuario);
    }
	
	public function findMovimentosByCartaoCreditoAndRange($cartaoCredito, $dataInicial, $dataFinal, $usuario){
    	return MovimentoDao::findMovimentosByCartaoCreditoAndRange($cartaoCredito, $dataInicial, $dataFinal, $usuario);
    }
	
	public function findMovimentosByFormaPagamentoAndRange($formaPagamento, $dataInicial, $dataFinal, $usuario){
    	return MovimentoDao::findMovimentosByFormaPagamentoAndRange($formaPagamento, $dataInicial, $dataFinal, $usuario);
    }
    
	 public function findMovimentosByFinalidadeAndRange($finalidade, $dataInicial, $dataFinal, $usuario){
    	return MovimentoDao::findMovimentosByFinalidadeAndRange($finalidade, $dataInicial, $dataFinal, $usuario);
    }
	
	 public function findMovimentosByFaturaCartao($fatura){
    	return MovimentoDao::findMovimentosByFaturaCartao($fatura);
    }
	
	public function cancelarProgramacao($movimento){
    	return MovimentoDao::cancelarProgramacao($movimento);
    }
    
    public function iniciarProgramacao($movimento, $periodicidade){
    	return MovimentoDao::iniciarProgramacao($movimento);
    }
    
    public function saveMovimentosParcelados($movimentos, $usuario){
    	foreach($movimentos as $movimento){
    		MovimentoDao::save($movimento, $movimento->idUsuario);
    	}
    	return $movimentos;
    }
    
	public function removeProximasParcelas($movimento, $usuario){
    	if ( $movimento->hashParcelas != '' && $movimento->hashParcelas != null ){
    		return MovimentoDao::removeProximasParcelas($movimento, $usuario);
    	}
    }
	
    public function removeParcelas($movimento, $usuario){
    	if ( $movimento->hashParcelas != '' && $movimento->hashParcelas != null ){
    		return MovimentoDao::removeParcelas($movimento, $usuario);
    	}
    }
    
    public function findParcelas($hash, $usuario){
    	return MovimentoDao::findParcelas($hash, $usuario);
    }

}
?>