<?php

require __DIR__.'/../../vendor/autoload.php';

use \Illuminate\Database\Capsule\Manager as DB;

use \App\Config\Config as Config;
use \Models\Movement as Movement;
use \Models\ScheduledMovement as ScheduledMovement;

//CALL CONFIGURATION LOGGER AND DATABASE
Config::configureServiceFactoryORM();

//=======================================================================

//LOAD SCHEDULED MOVEMENTS//
$scheduleds = ScheduledMovement::all()->where('movement.usuario', '3');

//ITERATE OVER SCHEDULED MOVEMENTS//
foreach ($scheduleds as $scheduled) {

	//CLONE MOVEMENT ORIGN
	$new_movement = $scheduled->movement->replicate();

	Config::logger()->addInfo("Start generating scheduled movement: " . $new_movement->descricao );

	//CONFIGURE NEW MATURITY DATE
	$lastGenerated = $scheduled->ultimaGeracao;
	$date_array = array('year'  => substr($lastGenerated,0,4), 
		                'month' => substr($lastGenerated,4,2), 
		                'day'   => substr($lastGenerated,6,2));

	if ( $date_array['month'] == "12" ){
		$year = $date_array['year'] + 1;
		$new_movement->vencimento = $year . '01' . $date_array['day'];
	}else{
		$new_movement->vencimento = $date_array['year'] . $date_array['month'] + 1 . $date_array['day'];
	}

	//SET NEW EMISSION DATE
	$new_movement->emissao = date('Ymd');
	$new_movement->status  = Movement::STATUS_TO_PAY;

	Config::logger()->addInfo("Last genereted: " . $lastGenerated .' ' . $new_movement->vencimento );

	//SET MOVEMENT ORIGN
	$new_movement->movimentoOrigem = $scheduled->movement->id;

	//SAVE NEW MOVEMENT
	Config::logger()->addInfo("Saving new movement scheduled for: " . $new_movement->descricao );
	$new_movement->push();

	//UPDATE LAST GENERETED DATE
	Config::logger()->addInfo("Updating last generated date...");
	$scheduled->ultimaGeracao = $new_movement->vencimento;
	$scheduled->save();

}

?>
