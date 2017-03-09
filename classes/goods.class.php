<?php 

/**
* Класс для работы с товарами
*/
class Goods
{

  private $node = NULL;
  private $is_new = false;

  /**
   * если код найден получаем объект указатель 
   * иначе создаем и получаем 
   * @param [type] $code код из соседнего объекта соединения
   */
  function __construct($code)
  {
    if (!empty($code)) {
      
      $query = db_select('uc_products', 'products')
        ->fields('products', array('nid', 'sell_price', 'model'))
        ->condition('products.model', $code)
        ->execute()
        ->fetchAll();


      if (count($query) == 1) {
        // drupal_set_message("Нода обновлена", 'status', FALSE);
      } elseif (count($query) == 0) {
        drupal_set_message("Нода не найдена создаем новую $code" , 'warning', FALSE);
        dpm($code);
      } else {
        drupal_set_message("ошибка: более одного значения в базе $code", 'error', FALSE);
      }
    } else {
      drupal_set_message("ошибка: пустое поле модели", 'error', TRUE);
    }
  }


  private function node_search_by_code() {}
  private function node_search_by_nid() {}

  public function getNid(){}
  public function getSell_price(){}
  public function getModel(){}

  public function getName(){}
  public function getStock(){}

}