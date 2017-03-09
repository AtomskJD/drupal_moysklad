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
* abstract conn 
*/
class Connector
{
  const BASEURL = 'https://online.moysklad.ru/api/remap/1.0/';
  protected $url;

  /**
   * set url for entity
   * @param string $url рабочай часть url
   */
  protected function setUrl($url)
  {
    $this->url = self::BASEURL . $url;
  }



  /**
   * основной обходчик данных
   * @return [object] возвращает объект из запрошеных данных
   */
  protected function getItemsInterface($offset = 0, $limit = 25)
  {
    $headers = array(
      'Content-Type:application/json',
      'Authorization: Basic '. base64_encode(variable_get('moysklad_login').":". variable_get('moysklad_pass') ) // <---
      );

      $url = $this->url . "?offset=$offset&limit=$limit";

      // dpm($url);

        $process = curl_init($url);

        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        // curl_setopt($process, CURLOPT_POST, 1);

        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($process);
        // dpm(json_decode($return));

        curl_close($process);

        return json_decode($return);
  }



  public function probeConnection()
  {
    return $this->getItemsInterface(0, 1)->meta;
  }

  protected function getSize()
  {
    return $this->getItemsInterface(0, 1)->meta->size;
  }




  /**
   * Получаем полтый перечень всех итемов (пока ограничение 1000)
   * @return [array] array of objects
   */
  public function getAllItems($superLimit)
  {
    $size = $this->getSize();
    $limit = 100;
    $offset = 0;
    $result;
    $t_start = microtime(true);
    while (($size > $offset) && ($offset < $superLimit)) {
      foreach ($this->getItemsInterface($offset, $limit)->rows as $row) {
        $result[] = $row;
      }


      $offset += $limit;
    }

    dpm(microtime(true) - $t_start, "request timer");
    return $result;
  }


  /**
   * [getQueriesList description]
   * @param  [type] $superLimit [description]
   * @param  [type] $params     [description]
   * @return [type]             array('offset' => $offset, 'limit' => $limit)
   */
  public function getQueriesList($superLimit, $params = NULL)
  {
    $result;

    if ($params) {
      $offset   = $params['offset'];
      $limit    = $params['limit'];
    } else {
      $limit = 100;
      $offset = 0;
    } 

    // получаем предварительный размер
    $size = $this->getSize();
    
    // построение цепочки запросов
    $t_start = microtime(true);
    while (($size > $offset) && ($offset < $superLimit)) {
      $result[] = array('offset' => $offset, 'limit' => $limit);
      $offset += $limit;
    }

    dpm(microtime(true) - $t_start, "request timer");
    return $result;
  }

  /**
   * метод постраничного запроса данных из очереди друпала
   * @param  [type] $superLimit [description]
   * @param  [type] $offset     параметр задан обязательным
   * @param  [type] $limit      параметр задан обязательным
   * @return [type]             [description]
   */
  public function getItems($offset, $limit)
  {
    return $this->getItemsInterface($offset, $limit)->rows;
  }



  /**
   * Постраничный вывод удаленных данных
   * @param  [type] $superLimit [description]
   * @return [type]             [description]
   */
  public function getAllItemsPaged($superLimit)
    {
      $size = $this->getSize();
      $limit = 50;
      $offset = 0;
      $result;

      while (($size > $offset) && ($offset < $superLimit)) {
        $t_start = microtime(true);

          $result[] = $this->getItemsInterface($offset, $limit)->rows;
          dpm(microtime(true) - $t_start, "page request timer :: ".$offset);

        $offset += $limit;


      }
      return $result;
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

class ReportConnector extends Connector
{
  
  public function __construct($entity){
    $this->setUrl('report/stock/' . $entity);
    // dpm($this->probeConnection());
    // dpm($this->getAllItems());
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


class GoodsReportConnector extends ReportConnector
{
  // набор обработчиков для интерфейса
  public function getQuantity($item){
    return $item->quantity;
  }
  public function getSell_price($item){
    return $item->salePrice;
  }
  public function getModel($item){
    if (!empty($item->code)) {
      return $item->code;
    } 
    elseif (!empty($item->article)) {
      return $item->article;
    }
  }
  public function getModel2($item){
    return $item->article;
  }

  public function getName($item) {
    return $item->name;
  }
}

 ?>