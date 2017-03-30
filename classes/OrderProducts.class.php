<?php

/**
* класс продуктов в заказ 
* альтернативно-тестовый класс для добавления товаров к существующему на моемскладе заказу
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


