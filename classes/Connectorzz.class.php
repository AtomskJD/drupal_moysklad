<?php

/**
* abstract connector 
*/
class Connectorzz
{
  function __construct()
  {
    $headers = array(
      'Content-Type:application/json',
      'Authorization: Basic '. base64_encode(variable_get('moysklad_login').":". variable_get('moysklad_pass') ) // <---
    );
    
      // $process = curl_init('https://online.moysklad.ru/api/remap/1.0/report/stock/all');
      $process = curl_init('https://online.moysklad.ru/api/remap/1.0/entity/store');
      curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($process, CURLOPT_HEADER, 0);
      curl_setopt($process, CURLOPT_TIMEOUT, 30);
      // curl_setopt($process, CURLOPT_POST, 1);

      curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
      $return = curl_exec($process);
      // dpm(json_decode($return));

      curl_close($process);
  }
}




/**
* test conn 
*/
class EntityConnector extends Connector
{
  
  public function __construct($entity){
    $this->setUrl('entity/' . $entity);
    // dpm($this->probeConnection());
    // dpm($this->getAllItems());
  }

}



/**
 * Уже тестовый
 */
class ReportConnector extends Connector
{
  
  public function __construct($entity){
    $this->setUrl('report/stock/' . $entity);
  }

  public function updateData($limit)
  {
    $size = $this->getSize();
    $limit = 100;
    $offset = 0;
    $result;

    while (($size > $offset) && ($offset < $limitdbea)) {
      foreach ($this->getItemsInterface($offset, $limit)->rows as $row) {
        $result[] = $row;
      }


      $offset += $limit;
    }
    return $result;
  }

}
