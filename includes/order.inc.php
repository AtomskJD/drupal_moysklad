<?php 

function surweb_moysklad_uc_checkout_complete ($order, $acc) {
	dpm($order);

	$myOrder = new OrderConnector();
	$myOrder->setOrder($order);

	dpm("order send");
}