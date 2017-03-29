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
        $url = $this->url . "?offset=$offset&limit=$limit&serach=$search";
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

/**
* Класс организации
*/
class Organization extends Connector
{
  function __construct()
  {
    $this->setEntity("organization");  
  }

  public function getOrganization()
  {
    return $this->getItems(0, 1);
  }

  public function getMeta()
  {
    return $this->getItems(0, 1)[0]->meta;
  }
}


/**
* Класс контрагента
*/
class Agent extends Connector
{

  protected $agent      = NULL;
  protected $agent_id   = NULL;
  protected $is_exists  = NULL;

   
   // первым делом ищем существующего контрагента
  function __construct($mail = false)
  {
    $this->setEntity("counterparty");
    if ($mail) {
      $agent_probe = $this->getByMail($mail);
      
      if ($agent_probe) {
        $this->is_exists = true;
        $this->agent = $agent_probe;
      } else $this->is_exists = false;
    } 
  }

  public function is_exists()
  {
    return $this->is_exists;
  }


  public function setAgent($params)
  {
    $body = array(
      "name"        => (string)($params->delivery_first_name . " " . $params->delivery_last_name),
      "email"       => (string)$params->primary_email,
      "phone"       => (string)$params->delivery_phone,
      "legalTitle"  => (string)$params->delivery_company,
      );
    // dpm($body);
    $this->agent = $this->setItemsInterface(json_encode($body));
  }


  public function getByMail($mail)
  {
    $size = $this->getSize();
    // dpm($size);
    $limit = 100;
    $offset = 0;
    $result;
    while ( $size > $offset ) {
      foreach ($this->getItemsInterface($offset, $limit)->rows as $row) {
        $result[] = $row;
        if ($row->email == $mail) {
          return $row;
        }
      }


      $offset += $limit;
    }

    return false;

  }

  public function createNew($params) 
  {
    // $params['mail']
    // $params['name']
    // $params['phone']
  }


  public function getMeta()
  {
    // dpm($this->agent, "agent - meta");
    if ($this->agent) {
      return $this->agent->meta;
    } else return false;
  }

  public function getAgent()
  {
    if ($this->agent) {
      return $this->agent;
    } else return false;
  }


}

/**
* класс продуктов в заказ
*/
class OrderProducts extends Connector
{
  protected $order_id;
  
  function __construct($order_id)
  {
    $this->order_id = $order_id;
    $this->setEntity("customerorder/".$order_id."/positions");
  }

  public function setProducts($products)
  {
          $curlOptions = array( 
              // CURLOPT_URL       => "https://online.moysklad.ru/api/remap/1.0/report/stock/all",
                  CURLOPT_HTTPHEADER      => array('Content-Type:application/json',
                    'Authorization: Basic '. base64_encode(variable_get('moysklad_login').":". variable_get('moysklad_pass') )),
                  CURLOPT_RETURNTRANSFER  => true,         // return web page 
                  CURLOPT_HEADER          => true,        // don't return headers 
                  CURLOPT_FOLLOWLOCATION  => true,         // follow redirects 
                  CURLOPT_ENCODING        => "",           // handle all encodings 
                  CURLOPT_USERAGENT       => "json client",     // who am i 
                  CURLOPT_AUTOREFERER     => true,         // set referer on redirect 
                  CURLOPT_CONNECTTIMEOUT  => 30,          // timeout on connect 
                  CURLOPT_TIMEOUT         => 30,          // timeout on response 
                  CURLOPT_MAXREDIRS       => 10,           // stop after 10 redirects 
                  CURLOPT_POST            => 1,            // i am sending post data 
                    // CURLOPT_POSTFIELDS     => $curl_body,    // this are my post vars 
                  CURLOPT_SSL_VERIFYHOST  => 0,            // don't verify ssl 
                  CURLOPT_SSL_VERIFYPEER  => false,        // 
                  CURLOPT_VERBOSE         => 1                // 
                );
      $ch = curl_init("https://online.moysklad.ru/api/remap/1.0/entity/customerorder/".$this->order_id.  "/positions");
      curl_setopt_array($ch, $curlOptions);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $products ));
          $reProducts = curl_exec($ch);

        curl_close($ch);

        // dpm($reProducts);
    // dpm($this->setItemsInterface($products));

  }
}


/**
* класс создания заказа
*/
class OrderConnector extends Connector
{

  function __construct()
  {
    $this->setEntity("customerorder");
  }


  public function setOrder($params)
  {

    if ($coupons = $params->data['coupons']) {
      foreach ($coupons as $coupon => $coupon_val) {
        switch ($coupon) {
          case '5PCOUPON':
            $coup = 5;
            $coup_desc = "Скидка 5%";
            break;
          
          case '10PCOUPON':
            $coup = 10;
            $coup_desc = "Скидка 10%";
            break;
          
          case '15PCOUPON':
            $coup = 15;
            $coup_desc = "Скидка 15%";
            break;
          
          case '20PCOUPON':
            $coup = 20;
            $coup_desc = "Скидка 20%";
            break;
          
          case '24PCOUPON':
            $coup = 24;
            $coup_desc = "Скидка 24%";
            break;
          

          default:
            $coup = 1;
            $coup_desc = "";
            break;
        }
      }
    }

    $t_start = microtime(true);
    $organization = new Organization();
    $agent = new Agent($params->primary_email);
    if (!$agent->is_exists()) {
      $agent->setAgent($params);
    }

    // dpm($agent->getMeta());

    foreach ($params->products as $order_product) {
      $good = new GoodsReportConnector('all');
      // сейчас используем поиск на стороне апи
      $good->search($order_product->model);
      // $good->findByModel($order_product->model);

      $products[] = array(
        "price" => (float)($order_product->price)*100,
        "discount" => $coup,
        "quantity" => (float)$order_product->qty,
        "assortment" => array("meta" => $good->getMeta()),
        );
      
    }
    $deliv = '';
    if(function_exists('uc_extra_fields_pane_value_load') && function_exists('_delivery_type_description')){
      $dd = uc_extra_fields_pane_value_load($order->order_id, 12, 1);
      $deliv = "Предпочтительный способ доставки: " . _delivery_type_description($dd->value) . "\n";
    }

    $comment = uc_order_comments_load($params->order_id);
    $description = $coup_desc . "\n" . $comment[0]->message . "\n" . $deliv;
    $name = time();
    $body = array('name' => "$name",
                  'organization' => array("meta" => $organization->getMeta()),
                  'agent' => array("meta" => $agent->getMeta()),
                  'positions' => $products,
                  'description' => $description,
                  );


    dpm($body, '$body');
    $respond = $this->setItemsInterface(json_encode($body));
    if ($respond->errors) {
      dpm(microtime(true) - $t_start, "new test timer errors");
      return array('errors' => $respond->errors);
    } else {

      // $order_products = new OrderProducts($respond->id);

      // $order_products->setProducts($products);


      dpm(microtime(true) - $t_start, "new test timer Ok");
      dpm($respond);
      return $respond;
    }
  }

}


class GoodsReportConnector extends Connector
{

  protected $item = NULL;

  public function __construct($entity){
    $this->setUrl('report/stock/' . $entity);
  }

  // набор обработчиков для интерфейса
  public function getQuantity($item = false){
    if ($this->item && !$item) {
      return $this->item->quantity;
    } else return $item->quantity;
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


  /**
   * находим в соединении нужный товар
   */
  public function findByModel($model)
  {
    $size = $this->getSize();
    $limit = 100;
    $offset = 0;
    $result;
    $t_start = microtime(true);
    while ($size > $offset) {
      foreach ($this->getItemsInterface($offset, $limit)->rows as $row) {
        if ($row->code == $model) {
          dpm(microtime(true) - $t_start, "search timer");
          $this->item = $row;
          return $row;
        }
      }


      $offset += $limit;
    }

    dpm(microtime(true) - $t_start, "search timer");
    return false;  
  }

  public function search($needle)
  {
    $t_start = microtime(true);

    foreach ($this->getItemsInterface(0, 100, $needle)->rows as $row) {
      if ($row->code == $needle) {
          dpm(microtime(true) - $t_start, "new search timer");
          $this->item = $row;
          return $row;
      }
    }
    
  }


  public function getMeta()
  {
    if ($this->item) {
      return $this->item->meta;
    }
  }

  public function getItem(){
    if ($this->item) {
      return $this->item;
    } else return false;
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
