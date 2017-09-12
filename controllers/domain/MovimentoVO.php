<?php
/*
 * Created on 01/03/2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 namespace controllers\domain{

	 class MovimentoVO
	 {
	 	public $id;
	 	public $emissao;
	 	public $vencimento;
	 	public $valor;
	 	public $status;
	 	public $operacao;
		public $formaPagamento;
	 	public $finalidade;
	 	public $contaBancaria;
		public $cartaoCredito;
	 	public $movimentoOrigem;
	 	public $descricao;
	 	public $parcela;
	 	public $fornecedor;
	 	public $hashParcelas;
	 	public $hashTransferencia;
		public $fatura;
	 	
	 	/*public function cloneMovimento(){
	 		$movimento = new MovimentoVO();
	 		$movimento->idEntity = $this->idEntity;
	 		$movimento->emissao = $this->emissao;
	 		$movimento->vencimento = $this->vencimento;
	 		$movimento->valor = $this->valor;
	 		$movimento->status = $this->status;
	 		$movimento->operacao = $this->operacao;
			$movimento->formaPagamento = $this->formaPagamento;
	 		$movimento->finalidade = $this->finalidade;
	 		$movimento->contaBancaria = $this->contaBancaria;
			$movimento->cartaoCredito = $this->cartaoCredito;
	 		$movimento->idUsuario = $this->idUsuario;
	 		$movimento->movimentoOrigem = $this->movimentoOrigem;
	 		$movimento->descricao = $this->descricao;
	 		$movimento->parcela = $this->parcela;
	 		$movimento->fornecedor = $this->fornecedor;
	 		$movimento->hashParcelas = $this->hashParcelas;
	 		$movimento->hashTransferencia = $this->hashTransferencia;
			$movimento->fatura = $this.fatura;
	 		return $movimento;
	 	}*/
	 	
	 }
}
 
?>
