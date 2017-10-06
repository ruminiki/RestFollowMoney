<?php


//$json = '{"descricao":"NETFLIX","emissao":"20170901","id":11860,"operacao":"DEBITO","status":"PAGO","usuario":3,"valor":22.9,"vencimento":"20171009"}';


$json = '{"cartaoCredito":{"dataFatura":5,"dataFechamento":29,"descricao":"RUMINIKI NUBANK","id":10,"limite":10050,"usuario":3},"descricao":"NETFLIX","emissao":"20170901","finalidade":{"descricao":"LAZER  ","id":22},"formaPagamento":{"descricao":"CRÉDITO","id":5,"sigla":"CC","usuario":3},"id":11860,"operacao":"DEBITO","status":"PAGO","usuario":3,"valor":22.9,"vencimento":"20171009"}';


$obj = json_decode($json, false);
echo $obj->descricao;

print_r($obj);