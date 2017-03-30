<?php 

function surweb_moysklad_uc_checkout_complete ($order, $acc) {
  // dpm($order);

  $myOrder = new OrderConnector();
  $myOrder->setOrder($order);

  // dpm("order send");
}

/**
  TODO: перенести в отдельный модуль и сделать проверку при вызове
*/
function _delivery_type_description ( $delivery_id ) {
  switch ( $delivery_id ) {
    case 'delivery_1':
      $result = 'самовывоз';
      break;
    case 'delivery_2':
      $result = 'доставка по городу';
      break;
    case 'delivery_3':
      $result = 'доставка до ТК';
      break;
    default:
      $result = 'Доставка до ТК';
      break;
  }

  return $result;
}

function _coupon_type_description( $coupon ) {
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
      $coup = 0;
      $coup_desc = "";
      break;
  }

  return array('coup' => $coup, 'coup_desc' => $coup_desc);
}