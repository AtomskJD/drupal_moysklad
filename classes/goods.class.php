<?php 

/**
* Класс для работы с товарами
*/
class Goods
{
  private $is_new     = NULL;

  private $code       = NULL;
  private $nid        = NULL;
  private $sell_price = NULL;
  private $stock      = NULL;
  private $name       = NULL;

  private $node       = NULL;

  /**
   * если код найден получаем объект указатель 
   * иначе создаем и получаем nid из созданного
   * @param [type] $code код из соседнего объекта соединения
   */
  function __construct($code)
  {
    if (!empty($code)) {
      $this->code = $code;

      $query = db_select('uc_products', 'products')
        ->fields('products', array('nid', 'sell_price', 'model'))
        ->condition('products.model', $code)
        ->execute()
        ->fetchAll();

        dvm(count($query), 'count');
      if (count($query) == 1) {
        $this->is_new = FALSE;
          // если есть валидный товар
        foreach ($query as $row) {
          $this->nid = $row->nid;
          $this->sell_price = $row->sell_price;
        }
      } elseif (count($query) == 0) {
        $this->is_new = TRUE;

        // TODO: создание новой ноды и товара
      } else {
        drupal_set_message("ошибка: более одного значения в базе $code", 'error', FALSE);
      }
    } else {
      drupal_set_message("ошибка: пустое поле модели", 'error', TRUE);
    }

  }


  private function node_search_by_code() {}
  private function node_search_by_nid() {}

  public function exists(){
    if (is_null($this->is_new)) {
      return false;
    } else {
      return true;
    }
  }

  public function is_new() {
    return $this->is_new;
  }

  public function getModel(){
    return $this->code;
  }
  
  public function getNid(){
    return $this->nid;
  }
  
  public function getSell_price(){
    return $this->sell_price;
  }
  
  public function getQuantity(){
    if ($this->nid && $this->code) {
       $query = db_select('uc_product_stock', 'stocks')
        ->fields('stocks', array('sku', 'nid', 'active', 'stock', 'threshold'))
        ->condition('stocks.sku', $this->code)
        ->condition('stocks.nid', $this->nid)
        ->execute()
        ->fetchAll();

        if (count($query) == 1) {
          foreach ($query as $row) {
            return $row->stock;
          }
        }
    }
    return $this->stock;
  }
  
  public function getName(){
    if ($this->nid) {
      $query = db_select('node', 'n')
        ->fields('n', array('nid', 'title', 'type', 'status'))
        ->condition('n.nid', $this->nid)
        ->condition('n.type', 'product')
        ->execute()
        ->fetchAll();

        if (count($query) == 1) {
          foreach ($query as $row) {
            return $row->title;
          }
        }
    }
    return $this->name;
  }

  public function setName($title) {
    if ($this->nid) {
      $query = db_select('node', 'n')
        ->fields('n', array('vid'))
        ->condition('n.nid', $this->nid)
        ->condition('n.type', 'product')
        ->execute()
        ->fetchAll();

        if (count($query) == 1) {
          foreach ($query as $row) {
            $vid = $row->vid;
            db_update('node')
              ->fields(array('title' => $title))
              ->condition('nid', $this->nid)
              ->execute();
            db_update('node_revision')
              ->fields(array('title' => $title))
              ->condition('nid', $this->nid)
              ->condition('vid', $vid)
              ->execute();


            return TRUE;
          }
        }
    }
  }



  public function setSell_price($price) {
    if ($this->nid) {
      $query = db_select('node', 'n')
        ->fields('n', array('vid'))
        ->condition('n.nid', $this->nid)
        ->condition('n.type', 'product')
        ->execute()
        ->fetchAll();

        if (count($query) == 1) {
          foreach ($query as $row) {
            $vid = $row->vid;
            db_update('uc_products')
              ->fields(array('sell_price' => $price))
              ->condition('nid', $this->nid)
              ->condition('vid', $vid)
              ->execute();


            return TRUE;
          }
        
      }
    }
  }



  public function setQuantity($quantity) {
    if ($this->nid && $this->code) {
      db_update('uc_product_stock')
              ->fields(array('stock' => $quantity))
              ->condition('nid', $this->nid)
              ->condition('sku', $this->code)
              ->execute();

        return TRUE;
    }
  }

}