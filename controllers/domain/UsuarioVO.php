<?php
/*
 * Created on 01/03/2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 include_once('EntityVO.php');
 
 class UsuarioVO extends EntityVO
 {
 	public $_explicitType="com.followMoney.domain.valueObjects.UsuarioVO";
 	public $nome;
 	public $login;
 	public $senha;
 	public $email;
 	public $documento;
 	public $ultimoAcesso;
 	
 	public function cloneUsuarioVO(){
 		$usr  = new UsuarioVO();
 		$usr->idEntity = $this->idEntity;
 		$usr->nome = $this->nome;
 		$usr->login = $this->login;
 		$usr->senha = $this->senha;
 		$usr->email = $this->email;
 		$usr->codigoAcesso = $this->codigoAcesso;
 		$usr->ultimoAcesso = $this->ultimoAcesso;
 		return $usr;
 	}
 }
 
?>
