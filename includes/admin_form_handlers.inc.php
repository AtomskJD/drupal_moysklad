<?php

function get_probe_connection () {
  $conn = new ReportConnector('all');
  dpm($conn->probeConnection());
}

function get_current_queue() {
  $queue = DrupalQueue::get('surweb_moysklad_goods');
  $queue->createQueue();


  return $queue->numberOfItems();
}

function _admin_check_connection () {

    $headers = array(
        'Content-Type:application/json',
        'Authorization: Basic '. base64_encode(variable_get('moysklad_login').":". variable_get('moysklad_pass') ) // <---
);
    // $process = curl_init('https://online.moysklad.ru/api/remap/1.0/report/stock/all');
    $process = curl_init('https://online.moysklad.ru/api/remap/1.0/entity/organization');
    curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        // curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($process);
        if (json_decode($return)->errors[0]) {
          drupal_set_message(json_decode($return)->errors[0]->error, 'warning', TRUE);
        } else {
          drupal_set_message("Соединение с сервером МоегоСклада установлено и проверено", 'status', TRUE);
          
        }
        // dpm(json_decode($return));

        curl_close($process);
}

function queue_info() {
  $queue = DrupalQueue::get('surweb_moysklad_goods');
  $queue->createQueue();
  return "<strong>Осталось элементов в очереди: </strong>"  .  $queue->numberOfItems();
}



/**
 * ЗАПУСКАЕТСЯ ПО CRON
 * постановка очереди ссылок на закачку остатков
 * и запуск очистки мусора
 * @return [type] [description]
 */
function goods_queue_set()
{
  $test = new ReportConnector('all');
  $listOfRequests = $test->getQueriesList(9999, array('offset' => 0, 'limit' => 50));
  $queue = DrupalQueue::get('surweb_moysklad_goods');
  $queue->createQueue();
  $ts = microtime(true);
  foreach ($listOfRequests as $request) {
    $queue->createItem($request);
  }


  // поддержание очереди в приемлемом размере
  $log_array = variable_get('moysklad_queue_log', '');
  if (count($log_array > 500)) {
    $log_array = array_chunk($log_array, 500);
    $log_array = array_pop($log_array);
  }
  
  variable_set('moysklad_queue_log', $log_array);

  variable_set( 'last_queue_info', "<strong>Создание очереди: </strong>" . date('d/m  H:i:s') . " Количество элементов в очереди: " . $queue->numberOfItems() );


  // теперь очередь очистки
  $node_clean_manager = variable_get('moysklad_clean_manager', NULL);
  if ((!is_null($node_clean_manager)) && (!empty($node_clean_manager))) {
    // TODO: выполнить очистку нод по NID и очистить moysklad_clean_manager

    foreach (array_flip($node_clean_manager) as $nid) {
      db_update('uc_product_stock')
              ->fields(array('stock' => 0))
              ->condition('nid', $nid)
              ->execute();
    }
  } 

  // TODO: небходимо наполнить moysklad_clean_manager

  $node_clean_manager = array();
    $query = db_select('node', 'n')
        ->fields('n', array('nid'))
        ->condition('n.type', 'product')
        ->execute()
        ->fetchAll();

        foreach ($query as $row) {
          $node_clean_manager[] = $row->nid;
        }

        // теперь надеемся что очередь сделает свое дело и 
        // удалит существующие записи из пременной
        variable_set('moysklad_clean_manager', array_flip($node_clean_manager));


  dpm(microtime(true) - $ts, 'queue timer');
  

}



function queue_claim() {
  $queue = DrupalQueue::get('surweb_moysklad_goods');

  while($item = $queue->claimItem()) {
      dpm($item);
      $queue->deleteItem($item);

    //Try saving the data.
    // if(saveRemoteItem($item->data)) {
      //Good, we succeeded.  Delete the item as it is no longer needed.
      // $queue->deleteItem($item);
    // }
    // else {
      //You might want to log to watchdog and delete the item 
      //anyway.  We'll just ignore the failure for our example.
    // }
  }
}

function run_cron() {
  if (function_exists('drupal_queue_cron_run')) {
    drupal_queue_cron_run();
  }
}

// get first 50
function get_items () {
  $goodsConnector = new GoodsReportConnector('all');
  foreach ($goodsConnector->getItems(0, 50) as $item) {

    $local_good = new Goods( $goodsConnector->getModel($item) );

    if ($local_good->exists()) {
      if ($local_good->is_new()) {
        dvm('это новый');
        $nid = $local_good->newItem($goodsConnector->getModel($item));
        $local_good->setName($goodsConnector->getName($item));
        $local_good->setSell_price($goodsConnector->getSell_price($item));
        $local_good->setQuantity($goodsConnector->getquantity($item));

        dpm($nid, "NEW nid");

      } else {

        // dpm($local_good->getModel(), 'model');
        // dpm($local_good->getSell_price(), 'local price');
        // dpm($goodsConnector->getSell_price($item), 'remote price');
        // dpm($local_good->getQuantity(), 'local quantity');
        // dpm($goodsConnector->getQuantity($item), 'remote quantity');
        // dpm($local_good->getName(), 'local name');
        // dpm($goodsConnector->getName($item), 'remote name');
      }
    }

  }
}

function createOneNew()
{
  dpm(time());
  $newGoodTest = new Goods();

  $newGoodTest->newItem();
  $newGoodTest->setName('DODOOD');
  $newGoodTest->setSell_price(1000);
  $newGoodTest->setQuantity(13);

}


function product_interface () {
  $pro = new Goods('TG-216-1139R-LD-E');

}


function createOneNewOrder() {
  $moyOrder = new OrderConnector();
  $o = array('delivery_first_name' => "dev@surweb.ru",
    'delivery_last_name' => "test",
    'primary_email' => "dev@surweb.ru",
    'delivery_phone' => "test",
    'delivery_company' => "companyy test",
    );
  dpm($moyOrder->setOrder($o));
}

function findByModel($model)
{
  $goods = new GoodsReportConnector('all');
  $goods->findByModel("TY11214BR");
  dpm($goods->getItem());
}

function checkAgent() {
  $params = array(
      "delivery_first_name"        => "name",
      "delivery_last_name"        => "name",
      "primary_email"       => "dev@surweb.ru",
      "delivery_phone"       => "888888888",
      "delivery_company"  => "company",
      );
  $agent = new Agent("dev@surweb.ru");
  dvm($agent->getAgent());
  if (!$agent->is_exists()) {
      dvm($params);
      $agent->setAgent($params);

      dvm($agent->getAgent());
    }
}

function checkOrg()
{
  // $node_clean_manager = array();
  //   $query = db_select('node', 'n')
  //       ->fields('n', array('nid'))
  //       ->condition('n.type', 'product')
  //       ->execute()
  //       ->fetchAll();

  //       foreach ($query as $row) {
  //         $node_clean_manager[] = $row->nid;
  //       }

  dpm(variable_get('moysklad_clean_manager', NULL));


}

function getLogs() {
  $log_array = variable_get('moysklad_queue_log', '');
  if (count($log_array > 500)) {
    $log_array = array_chunk($log_array, 500);
    $log_array = array_pop($log_array);
  }

  $log_array = array_reverse($log_array);

  return implode("\n", $log_array);
}