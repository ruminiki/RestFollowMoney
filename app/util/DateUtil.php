<?php
/*
 * Created on 27/03/2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class DateUtil{
 	
 	public static function getLastDayMonth($month){
		if ( $month == 12 ){
			return 31;
		}else{	
			$d = new Zend_Date();
			$d->setDay('01');
			$d->setMonth($month);
			for ( $i = 1; $i<=31; $i++ ){
				$d->add(1, Zend_Date::DAY);
				if ( $d->get(Zend_Date::MONTH) > $month ){
					return $i;
				}
			}
		}
 	}

 	public static function getMonth($date){
 		return substr($date,4,2);
 	}

 	public static function getYear($date){
 		return substr($date,0,4);
 	}

 	public static function mesReferenciaFromDateString($date){
 		//global $logger;
 		//$logger->addInfo('DateUtil:mesReferenciaFromDateString: ' . substr($date,4,2));
 		$month = DateUtil::getRepresentacaoMesString(substr($date,4,2));
 		$year  = substr($date,0,4);
 		//$logger->addInfo('DateUtil:mesReferenciaFromDateString: ' . $month);
 		return $month .'/'.$year;
 	}
 	
 	public static function parserToArray($date, $mask){
 		if ( strtolower($mask) == "yyyymmdd" ){
 			return array('year' => substr($date,0,4), 'month' => substr($date,4,2), 'day' => substr($date,6,2));
 		}
 		if ( strtolower($mask) == "ddmmyyyy" ){
 			return array('year' => substr($date,4,4), 'month' => substr($date,2,2), 'day' => substr($date,0,2));
 		}
 	}
	
	public static function getRepresentacaoMesString($mes){
		switch ($mes) {
			case '01':
				return "Jan";
				break;
			case '02':
				return "Fev";
				break;
			case '03':
				return "Mar";
				break;
			case '04':
				return "Abr";
				break;
			case '05':
				return "Mai";
				break;
			case '06':
				return "Jun";
				break;
			case '07':
				return "Jul";
				break;
			case '08':
				return "Ago";
				break;
			case '09':
				return "Set";
				break;
			case '10':
				return "Out";
				break;
			case '11':
				return "Nov";
				break;
			case '12':
				return "Dez";
				break;				
		}	
 	}
	
	public static function getMesAnterior($mes){
		switch ($mes) {
			case '01':
				return "12";
				break;
			case '02':
				return "01";
				break;
			case '03':
				return "02";
				break;
			case '04':
				return "03";
				break;
			case '05':
				return "04";
				break;
			case '06':
				return "05";
				break;
			case '07':
				return "05";
				break;
			case '08':
				return "07";
				break;
			case '09':
				return "08";
				break;
			case '10':
				return "09";
				break;
			case '11':
				return "10";
				break;
			case '12':
				return "11";
				break;				
		}	
 	}
	
	public static function getMesProximo($mes){
		switch ($mes) {
			case '01':
				return "02";
				break;
			case '02':
				return "03";
				break;
			case '03':
				return "04";
				break;
			case '04':
				return "05";
				break;
			case '05':
				return "06";
				break;
			case '06':
				return "07";
				break;
			case '07':
				return "08";
				break;
			case '08':
				return "09";
				break;
			case '09':
				return "10";
				break;
			case '10':
				return "11";
				break;
			case '11':
				return "12";
				break;
			case '12':
				return "01";
				break;				
		}	
 	}
	
	public static function getMesRepresentationToNumber($mes){
		switch ($mes) {
			case 'Jan':
				return "01";
				break;
			case 'Fev':
				return "02";
				break;
			case 'Mar':
				return "03";
				break;
			case 'Abr':
				return "04";
				break;
			case 'Mai':
				return "05";
				break;
			case 'Jun':
				return "06";
				break;
			case 'Jul':
				return "07";
				break;
			case 'Ago':
				return "08";
				break;
			case 'Set':
				return "09";
				break;
			case 'Out':
				return "10";
				break;
			case 'Nov':
				return "11";
				break;
			case 'Dez':
				return "12";
				break;
		}	
 	}
	
 }
 
?>
