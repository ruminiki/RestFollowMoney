<?php

namespace controllers{

		define("SQL_MOVIMENTO",
			"select m.id, 
					m.descricao, 
					m.usuario, 
					m.emissao, 
					m.vencimento, 
					m.valor, 
					m.status, 
					m.operacao, 
					m.movimentoOrigem,
					m.parcela, 
					m.hashParcelas, 
					m.hashTransferencia, 
					fl.id as idFinalidade, 
					fl.descricao as descricaoFinalidade,
					c.id as idContaBancaria, 
					c.descricao as descricaoContaBancaria, 
					c.numero as numeroContaBancaria,
					c.digito as digitoContaBancaria, 
					f.id as idFornecedor, 
					f.descricao as descricaoFornecedor,
					cr.id as idCartaoCredito, 
					cr.descricao as descricaoCartaoCredito, 
					cr.limite as limite, 
					cr.dataFatura as dataFatura, 
					cr.dataFechamento as dataFechamento,
					ft.id as idFatura, 
					ft.mesReferencia as mesReferencia, 
					ft.valor as valorFatura, 
					ft.valorPagamento as valorPagamentoFatura,
					fp.id as idFormaPagamento, 
					fp.descricao as descricaoFormaPagamento, 
					fp.sigla as siglaFormaPagamento 
			from movimento m 
				left join contaBancaria c on (c.id = m.contaBancaria and c.usuario = m.usuario)
				left join fornecedor f on (f.id = m.fornecedor and f.usuario = m.usuario)
				left join formaPagamento fp on (fp.id = m.formaPagamento and fp.usuario = m.usuario)
				left join cartaoCredito cr on (cr.id = m.cartaoCredito and cr.usuario = m.usuario)
				left join fatura ft on (ft.id = m.fatura and ft.usuario = m.usuario)
				inner join finalidade fl on (fl.id = m.finalidade and fl.usuario = m.usuario) "); 

	/*
	Classe finality
	*/
	class Movement{
		//Atributo para banco de dados
		private $PDO;
 
		/*
		__construct
		Conectando ao banco de dados
		*/
		function __construct(){
			$this->PDO = new \PDO('mysql:host=localhost;dbname=fmdb', 'root', ''); //Conexão
			$this->PDO->setAttribute( \PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION ); //habilitando erros do PDO
		}
		/*
		lista
		Listand pessoas
		*/
		public function list($user, $period){
			global $app;
			$sth = $this->PDO->prepare(SQL_MOVIMENTO . " WHERE m.usuario = :user and SUBSTRING(m.vencimento, 1, 6) = :period 
				                                         order by m.vencimento, m.emissao, m.descricao");
			$sth ->bindValue(':user',$user);
			$sth ->bindValue(':period',$period);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$result = resultToArray($result);
			$app->render('default.php',["data"=>$result],200); 
		}

		public function listByInvoice($invoice){
			global $app;
			$sth = $this->PDO->prepare(SQL_MOVIMENTO . " inner join movimentosFatura mf on mf.movimento = m.id 
														 where mf.fatura = :invoice
				                                         order by m.vencimento desc, m.emissao desc, m.descricao asc");
			$sth ->bindValue(':invoice',$invoice);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$result = resultToArray($result);
			$app->render('default.php',["data"=>$result],200); 
		}

		/*
		get
		param $id
		Pega pessoa pelo id
		*/
		public function get($id){
			global $app;
			$sth = $this->PDO->prepare(SQL_MOVIMENTO . " WHERE m.id = :id ");
			$sth ->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetch(\PDO::FETCH_ASSOC);
			$result = rowToObject($result);
			$app->render('default.php',["data"=>$result],200); 
		}
 
		/*
		nova
		Cadastra pessoa
		*/
		public function new(){
			global $app;
			$dados = json_decode($app->request->getBody(), true);
			$dados = (sizeof($dados)==0)? $_POST : $dados;
			$keys = array_keys($dados); //Paga as chaves do array
			/*
			O uso de prepare e bindValue é importante para se evitar SQL Injection
			*/
			$sth = $this->PDO->prepare("INSERT INTO movimento (".implode(',', $keys).") VALUES (:".implode(",:", $keys).")");
			foreach ($dados as $key => $value) {
				if ( is_array($value) ){
					$value = $value['id'];
				}
				$sth ->bindValue(':'.$key,$value);
			}
			$sth->execute();
			//Retorna o id inserido
			$app->render('default.php',["data"=>['id'=>$this->PDO->lastInsertId()]],200); 
		}
 
		/*
		editar
		param $id
		Editando pessoa
		*/
		public function edit($id){
			global $app;
			$dados = json_decode($app->request->getBody(), true);
			$dados = (sizeof($dados)==0)? $_POST : $dados;
			$sets = [];
			foreach ($dados as $key => $VALUES) {
				$sets[] = $key." = :".$key;
			}
 
			$sth = $this->PDO->prepare("UPDATE movimento SET ".implode(',', $sets)." WHERE id = :id");
			$sth ->bindValue(':id',$id);
			foreach ($dados as $key => $value) {
				if ( is_array($value) ){
					$value = $value['id'];
				}

				$sth ->bindValue(':'.$key,$value);
			}
			//Retorna status da edição
			$app->render('default.php',["data"=>['status'=>$sth->execute()==1]],200); 
		}
 
		/*
		excluir
		param $id
		Excluindo pessoa
		*/
		public function delete($id){
			global $app;
			$sth = $this->PDO->prepare("DELETE FROM movimento WHERE id = :id");
			$sth ->bindValue(':id',$id);
			$app->render('default.php',["data"=>['status'=>$sth->execute()==1]],200); 
		}
	}


	//========
	//===============CONVERTE RETORNO DO BANCO EM LISTA DE OBJTOS======================
	function resultToArray($result){
		$list = array();
		
		foreach ($result as $key => $value) {
			array_push($list, rowToObject($value));
		}

		return $list;
	}
	
	function rowToObject($row){
		$movimento                     = new \controllers\domain\MovimentoVO();
		$movimento->id      		   = $row['id'];
		$movimento->descricao 	 	   = $row['descricao'];
		$movimento->idUsuario		   = $row['usuario'];
		$movimento->emissao            = $row['emissao']; 
		$movimento->vencimento		   = $row['vencimento']; 
		
		$finalidade                    = new \controllers\domain\FinalidadeVO();
		$finalidade->id                = $row['idFinalidade'];
		$finalidade->descricao         = $row['descricaoFinalidade'];
		$finalidade->idUsuario		   = $row['usuario'];
		$movimento->finalidade		   = $finalidade;

		$contaBancaria                 = new \controllers\domain\ContaBancariaVO();
		$contaBancaria->id             = $row['idContaBancaria'];
		$contaBancaria->descricao      = $row['descricaoContaBancaria'];
		$contaBancaria->numero         = $row['numeroContaBancaria'];
		$contaBancaria->digito         = $row['digitoContaBancaria'];
		$contaBancaria->usuario        = $row['usuario'];
		$movimento->contaBancaria      = $contaBancaria;
		
		$fornecedor                    = new \controllers\domain\FornecedorVO();
		$fornecedor->id                = $row['idFornecedor'];
		$fornecedor->descricao         = $row['descricaoFornecedor'];
		$movimento->fornecedor         = $fornecedor;
		
		$movimento->movimentoOrigem    = $row['movimentoOrigem'];
		$movimento->parcela     	   = $row['parcela'];
		$movimento->valor		  	   = $row['valor'];
		$movimento->hashParcelas	   = $row['hashParcelas'];
		$movimento->hashTransferencia  = $row['hashTransferencia'];
		$movimento->status 		 	   = $row['status'];
		$movimento->operacao	 	   = $row['operacao'];
		
		$formaPagamento                = new \controllers\domain\FormaPagamentoVO();
		$formaPagamento->id            = $row['idFormaPagamento'];
		$formaPagamento->descricao     = $row['descricaoFormaPagamento'];
		$formaPagamento->sigla         = $row['siglaFormaPagamento'];
		$formaPagamento->usuario	   = $row['usuario'];
		$movimento->formaPagamento     = $formaPagamento;
		
		$cartaoCredito                 = new \controllers\domain\CartaoCreditoVO();
		$cartaoCredito->id             = $row['idCartaoCredito'];
		$cartaoCredito->descricao      = $row['descricaoCartaoCredito'];
		$cartaoCredito->limite         = $row['limite'];
		$cartaoCredito->dataFatura     = $row['dataFatura'];
		$cartaoCredito->dataFechamento = $row['dataFechamento'];
		$cartaoCredito->usuario  	   = $row['usuario'];
		$movimento->cartaoCredito      = $cartaoCredito;
		
		$fatura                 	   = new \controllers\domain\FaturaVO();
		$fatura->id                    = $row['idFatura'];
		$fatura->mesReferencia         = $row['mesReferencia'];
		$fatura->valor                 = $row['valorFatura'];
		$fatura->valorPagamento        = $row['valorPagamentoFatura'];
		$fatura->usuario	           = $row['usuario'];
		$movimento->fatura             = $fatura;

		return $movimento;
	}
}