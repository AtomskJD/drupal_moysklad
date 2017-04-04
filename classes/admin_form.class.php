<?php

/**
* Класс для страницы администрирования
*/
class AdminPage
{

  private $form = array();
  

  function __construct()
  {
    // Панель информации о соединении
    $this->form['connection'] = array(
        '#type' => 'fieldset',
        '#title' => 'Настройки пользователя',
    );

     $this->form['connection']['moysklad_login'] = array(
          '#type' => 'textfield',
          '#title' => "Аккаунт на moysklad",
          '#default_value' => variable_get('moysklad_login', 'user@name'),
          '#size' => 30,
          '#maxlength' => 30,
          '#description' => "Имя пользователя вида user@name",
          '#required' => TRUE,
      );
    $this->form['connection']['moysklad_pass'] = array(
          '#type' => 'textfield',
          '#title' => "Пароль пользователя moysklad",
          '#default_value' => variable_get('moysklad_pass', ''),
          '#size' => 30,
          '#maxlength' => 30,
          '#required' => TRUE,
      );

    $this->form['connection']['check_connection'] = array(
      '#type' => 'submit',
      '#value' => 'Проверить логин',
      '#weight' => 15,
      '#submit' => array('_admin_check_connection'),
      );
   
    // Панель информации об очереди
    $this->form['queue_info_panel'] = array(
      '#type' => 'fieldset',
      '#title' => 'История изменений',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#weight' => 15,
    );

    $this->form['queue_info_panel']['queue_log'] = array(
      '#title' => '',
      '#type' => 'textarea',
      '#description' => 'Последние 500 изменений',
      '#default_value' => getLogs(),
      );


    $this->form['queue_info'] = array(
      '#markup' => "<fieldset>" . 
        "<p>" . queue_info() . "</p>" . 
        "<p>" . variable_get('last_queue_info', 'нет информации о последней очереди') . "</p>" . 
        "<p>" . variable_get('last_worker_info', 'нет информации о последних операциях над очередью') . "</p>" . 
      "</fieldset>",
    );



/*
      // КНОПКА проверки ОЧЕРЕДИ
      $this->form['goods_queue_set'] = array(
              '#type' => 'submit',
              '#value' => 'Постановка списка в очередь',
              '#weight' => 15,
              '#submit' => array('goods_queue_set'),
              );*/

/*        // КНОПКА очистки ОЧЕРЕДИ
      $this->form['queue_claim'] = array(
              '#type' => 'submit',
              '#value' => 'Очистить очередь',
              '#weight' => 25,
              '#submit' => array('queue_claim'),
              );
*/
      /*// КНОПКА очистки запуска КРОНА
      $this->form['run_cron'] = array(
              '#type' => 'submit',
              '#value' => 'запустить крон',
              '#weight' => 35,
              '#submit' => array('run_cron'),
              );*/


/*        // КНОПКА очистки запуска КРОНА
      $this->form['get_items'] = array(
              '#type' => 'submit',
              '#value' => 'GET ITEMS 50',
              '#weight' => 35,
              '#submit' => array('get_items'),
              );
*/
/*        // КНОПКА проверка
      $this->form['probe_conn'] = array(
              '#type' => 'submit',
              '#value' => 'Probe Connection',
              '#weight' => 40,
              '#submit' => array('get_probe_connection'),
              );
*/
/*        // КНОПКА проверки объекта базы
      $this->form['product_interface'] = array(
              '#type' => 'submit',
              '#value' => 'Probe PRODUCT',
              '#weight' => 50,
              '#submit' => array('product_interface'),
              );
*/

/*        // КНОПКА для создания нового товара
      $this->form['create_new_product'] = array(
              '#type' => 'submit',
              '#value' => 'CREATE new PRODUCT',
              '#weight' => 55,
              '#submit' => array('createOneNew'),
              );

*/
/*       // КНОПКА для создания нового заказа
      $this->form['create_new_product'] = array(
              '#type' => 'submit',
              '#value' => 'CREATE new ORDER',
              '#weight' => 40,
              '#submit' => array('createOneNewOrder'),
              );
*/
      
  /*
      // КНОПКА для поиска товара
      $this->form['find_by_model'] = array(
              '#type' => 'submit',
              '#value' => 'find_by_model',
              '#weight' => 45,
              '#submit' => array('findByModel'),
              );

      */
  
  /*        // КНОПКА для поиска товара
      $this->form['find_agent'] = array(
              '#type' => 'submit',
              '#value' => 'find agent',
              '#weight' => 45,
              '#submit' => array('checkAgent'),
              );
*/

/*      // КНОПКА тестирования всякого
      $this->form['find_organization'] = array(
              '#type' => 'submit',
              '#value' => 'find org',
              '#weight' => 45,
              '#submit' => array('checkOrg'),
              );*/
      
  }





  public function getForm() 
  {
      return $this->form;
  }
}

