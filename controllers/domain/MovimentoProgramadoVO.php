<?php
/*
 * Created on 01/03/2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 include_once('EntityVO.php');
  
 class MovimentoProgramadoVO extends EntityVO{
 
	public $_explicitType = "com.followMoney.domain.valueObjects.MovimentoProgramadoVO";
	public $inicio;
 	public $movimento;
 	public $ultimaGeracao;
 	public $periodicidade;
	public $fim;
	
 }
 
?>
