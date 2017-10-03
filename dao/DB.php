<?php
  /*
   * Created on 01/03/2011
   *
   * To change the template for this generated file go to
   * Window - Preferences - PHPeclipse - PHP - Code Templates
   */

  use \Slim\PDO\Database as Database;

  class DB{

    static $PDO;

    const DSN = 'mysql:host=localhost;dbname=fmdb;charset=latin1';  
    const USR = 'root';
    const PWD = '';

   private static function PDO()
      {
          if (DB::$PDO === null)
              DB::$PDO = new Database(DB::DSN, DB::USR, DB::PWD);
          return DB::$PDO;
      }

    public static function listByUser($table, $user){
        $stm = DB::PDO()->select()->from($table)->where('usuario', '=', $user);
        return $stm->execute()->fetchAll();
    }

    public static function findById($table, $id){
        $stm = DB::PDO()->select()->from($table)->where('id', '=', $id);
        return $stm->execute()->fetch();
    }

    public static function insert($table, $arrayColumns, $arrayValues){
      return DB::PDO()->insert($arrayColumns)->into($table)->values($arrayValues)->execute(false);
    }

    public static function update($table, $arraySet, $id){
      return DB::PDO()->update($arraySet)->table($table)->where('id', '=', $id)->execute();
    }

    public static function delete($table, $id){
      return DB::PDO()->delete()->from($table)->where('id', '=', $id);
    }

    public static function executeQuery($query, $params){
      $stm = DB::PDO()->prepare($query);
      foreach ($params as $key => $value) {
        $stm->bindParam($key,$value);
      }

      $stm->execute();
      return $stm->fetchAll();
    }

  }
   
?>
