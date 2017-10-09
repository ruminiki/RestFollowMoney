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
    const PWD = 'dust258';

   public static function PDO(){
      if (DB::$PDO === null){
        DB::$PDO = new Database(DB::DSN, DB::USR, DB::PWD);
        DB::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        DB::$PDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
      }
      return DB::$PDO;
    }

    public static function beginTransaction(){
      DB::PDO()->beginTransaction();
    }

    public static function commit(){
      DB::PDO()->commit();
    }

    public static function rollBack(){
      DB::PDO()->rollBack();
    }

    public static function listByUser($table, $user){
        $stm = DB::PDO()->select()->from($table)->where('usuario', '=', $user)->orderBy('descricao', 'ASC');;
        return $stm->execute()->fetchAll();
    }

    public static function findById($table, $id){
        $stm = DB::PDO()->select()->from($table)->where('id', '=', $id);
        return $stm->execute()->fetch();
    }

    public static function insert($table, $arrayColumns, $arrayValues){
      global $logger;
      $logger->addInfo('DB Insert: insert into table: ' . $table );
      try{
        DB::PDO()->insert($arrayColumns)->into($table)->values($arrayValues)->execute(false);
        $logger->addInfo('DB Insert: inserted ID: ' . DB::PDO()->lastInsertId() );
        return DB::PDO()->lastInsertId();
      }catch(\PDOExeption $e){
        $logger->addError('DB Insert: error: ' . $e->getMessage() );
        return 'error';
      }
    }

    public static function update($table, $arraySet, $id){
      global $logger;
      try{
        $logger->addInfo('DB Update: updating ' . $table . ' ' . $id );
        return DB::PDO()->update($arraySet)->table($table)->where('id', '=', $id)->execute();
      }catch(\PDOExeption $e){
        $logger->addError('DB Update: error: ' . $e->getMessage() );
        return 'error';
      }
    }

    public static function queryUpdate($query, $params){
      global $logger;
      try{
        $logger->addInfo('DB Update: updating ' . $query );
        $stm = DB::PDO()->prepare($query);
        foreach ($params as $key => $value) {
          $stm->bindValue($key,$value);
        }
        $stm->execute();
      }catch(\PDOExeption $e){
        $logger->addError('DB Update: error: ' . $e->getMessage() );
        return 'error';
      }
    }

    public static function delete($table, $id){
      global $logger;
      $logger->addInfo('DB Delete: deleting ' . $table . ' ' . $id );
      $stm = DB::PDO()->delete()->from($table)->where('id', '=', $id);
      return $stm->execute();
    }

    public static function queryDelete($query, $params){
      global $logger;
      try{
        $logger->addInfo('DB Delete: deleting... ' . $query );
        $stm = DB::PDO()->prepare($query);
        foreach ($params as $key => $value) {
          $stm->bindValue($key,$value);
        }
        $stm->execute();
      }catch(\PDOExeption $e){
        $logger->addError('DB Delete: error: ' . $e->getMessage() );
        return 'error';
      }
    }

    public static function executeQuery($query, $params){
      global $logger;
      $stm = DB::PDO()->prepare($query);
      foreach ($params as $key => $value) {
        $stm->bindValue($key,$value);
      }

      //$logger->addInfo('Query: ' . $stm->queryString);

      $stm->execute();
      return $stm->fetchAll();
    }

    public static function fetchUnique($query, $params){
      $stm = DB::PDO()->prepare($query);
      foreach ($params as $key => $value) {
        $stm->bindValue($key,$value);
      }

      $stm->execute();
      return $stm->fetch();
    }

  }
   
?>
