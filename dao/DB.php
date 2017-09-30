<?php
/*
 * Created on 01/03/2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

//define("PDO", new \PDO('mysql:host=localhost;dbname=fmdb', 'root', ''));
use \Slim\PDO\Database as Database;

class DB{

  static $PDO;

  const DSN = 'mysql:host=localhost;dbname=fmdb;charset=utf8';  
  const USR = 'root';
  const PWD = '';

  private static function PDO()
    {
        if (DB::$PDO == null)
            DB::$PDO = new Database(DB::DSN, DB::USR, DB::PWD);
        return DB::$PDO;
    }

  public static function listByUser($table, $user){
      $stm = DB::PDO()->select()->from($table)->where('usuario', '=', $user);
      return $stm->execute()->fetchAll();
  }
    
}
 
?>
