<?php 

/**
* abstract conn 
*/
class Connector
{
  const BASEURL = 'https://online.moysklad.ru/api/remap/1.1/';
  protected $url;

  /**
   * set url for entity
   * @param string $url рабочай часть url
   */
  protected function setUrl($url)
  {
    $this->url = self::BASEURL . $url;
  }
  protected function setEntity($entity)
  {
    dpm($this->url = self::BASEURL . "entity/" . $entity);
  }

  // // впринципе покрывает 90% API
  // function __construct($entity)
  // {
  //   $this->setUrl('entity/' . $entity);
  // }



  /**
   * основной обходчик данных
   * @return [object] возвращает объект из запрошеных данных
   */
  protected function getItemsInterface($offset = 0, $limit = 25, $search = NULL)
  {
    $headers = array(
      'Content-Type:application/json',
      'Authorization: Basic '. base64_encode(variable_get('moysklad_login').":". variable_get('moysklad_pass') ) // <---
      );

      if (is_null($search)) {
        $url = $this->url . "?offset=$offset&limit=$limit";
      } else {
        $url = $this->url . "?offset=$offset&limit=$limit&search=$search";
      }

      dpm($url);

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

  /**
   * Защищенный интерфейс для записи в мойсклад
   */
  protected function setItemsInterface($body)
  {
        $headers = array(
      'Content-Type:application/json',
      'Authorization: Basic '. base64_encode(variable_get('moysklad_login').":". variable_get('moysklad_pass') ) // <---
      );

      $url = $this->url;

      // dpm($url);

        $process = curl_init($url);

        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_POSTFIELDS, $body);
        // curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "json client");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_VERBOSE, 1);


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

  public function getMeta() {
    return $this->getItemsInterface(0, 1)->meta;

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

