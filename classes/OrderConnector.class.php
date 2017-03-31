<?php

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
    $coup = 0;
    $coup_desc = "";
    if ($coupons = $params->data['coupons']) {
      foreach ($coupons as $coupon => $coupon_val) {
        if (function_exists('_coupon_type_description')) {
          $coupon = _coupon_type_description($coupon);
          $coup = $coupon['coup'];
          $coup_desc = $coupon['coup_desc'];
        }
      }
    }

    $t_start = microtime(true);
    $organization = new Organization();
    $agent = new Agent($params->primary_email);
    if (!$agent->is_exists()) {
      $agent->setAgent($params);
    }

    // var_dump($agent->getMeta());

    foreach ($params->products as $order_product) {
      $good = new GoodsReportConnector('all');
      // сейчас используем поиск на стороне апи
      $good->search($order_product->model);
      // $good->findByModel($order_product->model);

      if ($coup) {
        $products[] = array(
          "price" => (float)($order_product->price)*100,
          "discount" => $coup,
          "quantity" => (float)$order_product->qty,
          "reserve" => (float)$order_product->qty,
          "assortment" => array("meta" => $good->getMeta()),
        );

      } else {        

        $products[] = array(
          "price" => (float)($order_product->price)*100,
          "quantity" => (float)$order_product->qty,
          "reserve" => (float)$order_product->qty,
          "assortment" => array("meta" => $good->getMeta()),
        );
      }
      
    }
    $deliv = '';

    if(function_exists('uc_extra_fields_pane_value_load') && function_exists('_delivery_type_description')){
      $dd = uc_extra_fields_pane_value_load($params->order_id, 12, 1);
      $deliv = "Предпочтительный способ доставки: " . _delivery_type_description($dd->value) . "\n";
    }

    ////////////////////////////////////////
    // шапка Комментария с номером заказа //
    ////////////////////////////////////////
    $description = "Оформлен новый заказ №".$params->order_id." на сайте ".variable_get('site_name', 'Drupal') ."\n";
    if ($coup_desc) {
      $description .= "Клиенту предоставлена скидка: " . $coup_desc . "\n";
    }

    /////////////////////////
    // адрес в Комментарий //
    /////////////////////////
    $description .= $deliv;
    $description .= "Адрес доставки: " . $params->delivery_city . " " . $params->delivery_street1 . " " . $params->delivery_street2 . "\n";


    /////////////////////////////////////////
    // комментарий к заказу в Комментарий  //
    /////////////////////////////////////////
    $comment = uc_order_comments_load($params->order_id);
    if ($comment[0]->message) {
      $description .= "Комментарий клиента: " . $comment[0]->message . "\n";
    }


    // имя заказа присваивает сам мойсклад
    $body = array(
                  'organization' => array("meta" => $organization->getMeta()),
                  'agent' => array("meta" => $agent->getMeta()),
                  'positions' => $products,
                  'description' => $description,
                  );


    $respond = $this->setItemsInterface(json_encode($body));


    if ($respond->errors) {
      // dpm(microtime(true) - $t_start, "new test timer errors");
  
        drupal_set_message($respond->errors[0]->error, 'error', TRUE);
      return array('errors' => $respond->errors);
    } else {

      // $order_products = new OrderProducts($respond->id);

      // $order_products->setProducts($products);


      // dpm(microtime(true) - $t_start, "new test timer Ok");
      // dpm($respond);
      return $respond;
    }
  }

}
