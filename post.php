<?php 
/*$ch = curl_init();
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

$params = array(
    "id"=>11870,
	"descricao"=>"TAXA TED",
	"idUsuario"=>3,
	"emissao"=>"20170902",
	"vencimento"=>"20170831",
	"movimentoOrigem"=>0,
	"parcela"=>"",
	"valor"=>9.55,
	"hashParcelas"=>"",
	"hashTransferencia"=>"",
	"status"=>"PAGO",
	"operacao"=>"DEBITO",
	"finalidade"=>["id"=>34,
		"descricao"=>"TAXAS BANC\u00c1RIAS    ",
		"idUsuario"=>3],
	"contaBancaria"=>["id"=>81,
		"descricao"=>"ITA\u00da",
		"numero"=>"00882",
		"digito"=>"3",
		"usuario"=>3],
	"formaPagamento"=>["id"=>12,
		"descricao"=>"D\u00c9BITO",
		"sigla"=>"CD",
		"usuario"=>3]
);
curl_setopt($ch,CURLOPT_URL,"http://localhost:8888/movements/11870");
curl_setopt($ch,CURLOPT_POST,true);
curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($params));
$result = curl_exec($ch);

echo $result;

*/

/*$params = array(
    "id"=>11870,
	"descricao"=>"TESTE UPDATE",
	"idUsuario"=>3,
	"emissao"=>"20170902",
	"vencimento"=>"20170831",
	"movimentoOrigem"=>0,
	"parcela"=>"",
	"valor"=>9.55,
	"hashParcelas"=>"",
	"hashTransferencia"=>"",
	"status"=>"PAGO",
	"operacao"=>"DEBITO",
	"finalidade"=>["id"=>34,
		"descricao"=>"TAXAS BANC\u00c1RIAS    ",
		"idUsuario"=>3],
	"contaBancaria"=>["id"=>81,
		"descricao"=>"ITA\u00da",
		"numero"=>"00882",
		"digito"=>"3",
		"usuario"=>3],
	"formaPagamento"=>["id"=>12,
		"descricao"=>"D\u00c9BITO",
		"sigla"=>"CD",
		"usuario"=>3]
);*/

$params = '{"id":11870,"descricao":"TAXA TED","idUsuario":3,"emissao":"20170902","vencimento":"20170831","movimentoOrigem":0,"parcela":"","valor":9.55,"hashParcelas":"","hashTransferencia":"","status":"PAGO","operacao":"DEBITO","finalidade":{"id":34,"descricao":"TAXAS BANC\u00c1RIAS    ","idUsuario":3},"contaBancaria":{"id":81,"descricao":"ITA\u00da","numero":"00882","digito":"3","usuario":3},"formaPagamento":{"id":12,"descricao":"D\u00c9BITO","sigla":"CD","usuario":3}}';

$data = json_decode($params, TRUE);

$url = 'http://localhost:8888/movements/11870';
$query = http_build_query($data);
$ch    = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_URL, $url);
//curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
$response = curl_exec($ch);
curl_close($ch);
echo $response;