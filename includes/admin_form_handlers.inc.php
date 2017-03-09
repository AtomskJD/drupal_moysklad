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
    $process = curl_init('https://online.moysklad.ru/api/remap/1.0/entity/store');
    curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        // curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($process);
        dpm(json_decode($return));

        curl_close($process);
}

function queue_info() {
  $queue = DrupalQueue::get('surweb_moysklad_goods');
  $queue->createQueue();
  return "<strong>Осталось элементов в очереди: </strong>"  .  $queue->numberOfItems();
}

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

  // dpm(microtime(true) - $ts, 'queue timer');

  variable_set( 'last_queue_info', "<strong>Создание очереди: </strong>" . date('d/m  H:i:s') . " Количество элементов в очереди: " . $queue->numberOfItems() );

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
    // dvm($goodsConnector->getModel($item), "getModel");
    // dvm($goodsConnector->getModel2($item), "getModel2");
    // dpm($goodsConnector->getModel($item), 'getModel');

    $local_good = new Goods( $goodsConnector->getModel($item) );

    if ($local_good->exists()) {
      if ($local_good->is_new()) {
        dvm('это новый');
      }
      dvm($local_good->getModel(), 'model');
      dvm($local_good->getNid(), 'nid');
      dvm($local_good->getSell_price(), 'price');
      dvm($local_good->getStock(), 'stock');
      dvm($local_good->getName(), 'name');
    }

  }
}


function product_interface () {
  $pro = new Goods('TG-216-1139R-LD-E');

}