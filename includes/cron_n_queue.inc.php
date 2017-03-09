<?php 

// dpm('queque inc');

function surweb_moysklad_cron_queue_info() {
  $queues = array();
  $queues['surweb_moysklad_goods'] = array(
    'worker callback' => 'queueWorker_goods', //function to call for each item
    'time' => 60, //seconds to spend working on the queue
  );
  return $queues;
}


function queueWorker_goods($data) {
	$goodsConnector = new ReportConnector('all');
	$ts = microtime(true);
	$goodsConnector->getItems($data['offset'], $data['limit']);

  

	variable_set('last_worker_info', "<strong>Выполнение очереди: </strong>" . date('d/m  H:i:s') . " Время выполнения: " . (microtime(true) - $ts) . " queried offset " . $data['offset']);
}

	