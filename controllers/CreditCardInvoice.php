<?php
namespace controllers{

	define("SQL_FATURA",
		"select 
			f.id, 
			f.emissao, 
			f.vencimento, 
			f.emissao, 
			coalesce(f.valor, ((select sum(valor) from movimento m 
			                    inner join movimentosFatura mf on mf.movimento = m.id 
			                    where m.operacao = 'DEBITO' and mf.fatura = f.id) - 
			                    coalesce((select sum(valor) from movimento m 
			                    inner join movimentosFatura mf on mf.movimento = m.id 
			                    where m.operacao = 'CREDITO' and mf.fatura = f.id),0))) as valor, 
			f.valorPagamento, 
			f.usuario as usuario, 
			f.mesReferencia as mesReferencia, 
			f.status as status, 
		    c.id as idContaBancaria, 
		    c.descricao as descricaoContaBancaria, 
		    c.numero as numeroContaBancaria, 
		    c.digito as digitoContaBancaria,
		    cr.id as idCartaoCredito, 
		    cr.descricao as descricaoCartaoCredito,
		    cr.limite as limite, 
		    cr.dataFatura as dataFatura, 
		    cr.dataFechamento as dataFechamento,
		    fp.id as idFormaPagamento, 
		    fp.descricao as descricaoFormaPagamento, 
		    fp.sigla as siglaFormaPagamento 
		from fatura f 
		left join contaBancaria c on (c.id = f.contaBancaria and c.usuario = f.usuario)
		left join formaPagamento fp on (fp.id = f.formaPagamento and fp.usuario = f.usuario)
		inner join cartaoCredito cr on (cr.id = f.cartaoCredito and cr.usuario = f.usuario)"); 

	/*
	Classe finality
	*/
	class CreditCardInvoice{
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
		public function list($creditCard){
			global $app;
			$sth = $this->PDO->prepare(SQL_FATURA . " WHERE cartaoCredito = :creditCard order by vencimento desc");
			$sth ->bindValue(':creditCard',$creditCard);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$result = $this->resultToArray($result);
			$app->render('default.php',["data"=>$result],200); 
		}

		/*
		get
		param $id
		Pega pessoa pelo id
		*/
		public function get($id){
			global $app;
			$sth = $this->PDO->prepare("SELECT * FROM fatura WHERE id = :id");
			$sth ->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetch(\PDO::FETCH_ASSOC);
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
			$sth = $this->PDO->prepare("INSERT INTO fatura (".implode(',', $keys).") VALUES (:".implode(",:", $keys).")");
			foreach ($dados as $key => $value) {
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
 
			$sth = $this->PDO->prepare("UPDATE fatura SET ".implode(',', $sets)." WHERE id = :id");
			$sth ->bindValue(':id',$id);
			foreach ($dados as $key => $value) {
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
			$sth = $this->PDO->prepare("DELETE FROM fatura WHERE id = :id");
			$sth ->bindValue(':id',$id);
			$app->render('default.php',["data"=>['status'=>$sth->execute()==1]],200); 
		}

		public function resultToArray($result){
			$list = array();
		
			foreach ($result as $key => $value) {
				array_push($list, $this->rowToObject($value));
			}

			return $list;
		}
		
		function rowToObject($row){
			$fatura                        = new \controllers\domain\FaturaVO();
			$fatura->id                    = $row['id'];
			$fatura->emissao               = $row['emissao'];
			$fatura->idUsuario             = $row['usuario'];
			$fatura->vencimento			   = $row['vencimento'];
			$fatura->valor                 = $row['valor'];
			$fatura->valorPagamento        = $row['valorPagamento'];
			$fatura->mesReferencia         = $row['mesReferencia'];
			$fatura->status                = $row['status'];
			
			$cartaoCredito                 = new \controllers\domain\CartaoCreditoVO();
			$cartaoCredito->id             = $row['idCartaoCredito'];
			$cartaoCredito->descricao      = $row['descricaoCartaoCredito'];
			$cartaoCredito->limite         = $row['limite'];
			$cartaoCredito->dataFatura     = $row['dataFatura'];
			$cartaoCredito->dataFechamento = $row['dataFechamento'];
			$cartaoCredito->idUsuario	   = $row['usuario'];
			$fatura->cartaoCredito         = $cartaoCredito;
			
			$formaPagamento                = new \controllers\domain\FormaPagamentoVO();
			$formaPagamento->id            = $row['idFormaPagamento'];
			$formaPagamento->descricao     = $row['descricaoFormaPagamento'];
			$formaPagamento->sigla         = $row['siglaFormaPagamento'];
			$formaPagamento->idUsuario	   = $row['usuario'];
			$fatura->formaPagamento        = $formaPagamento;
			
			$contaBancaria                 = new \controllers\domain\ContaBancariaVO();
			$contaBancaria->id             = $row['idContaBancaria'];
			$contaBancaria->descricao      = $row['descricaoContaBancaria'];
			$contaBancaria->numero         = $row['numeroContaBancaria'];
			$contaBancaria->idUsuario      = $row['usuario'];
			$fatura->contaBancaria         = $contaBancaria;
			
			return $fatura;
		}
	}
}