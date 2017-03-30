<?php

class GoodsReportConnector extends Connector
{

  protected $item = NULL;

  public function __construct($entity){
    $this->setUrl('report/stock/' . $entity);
  }

  // набор обработчиков для интерфейса
  public function getQuantity($item = false){
    if ($this->item && !$item) {
      return $this->item->quantity;
    } else return $item->quantity;
  }
  public function getSell_price($item){
    return $item->salePrice;
  }
  public function getModel($item){
    if (!empty($item->code)) {
      return $item->code;
    } 
    elseif (!empty($item->article)) {
      return $item->article;
    }
  }
  public function getModel2($item){
    return $item->article;
  }

  public function getName($item) {
    return $item->name;
  }


  /**
   * находим в соединении нужный товар
   */
  public function findByModel($model)
  {
    $size = $this->getSize();
    $limit = 100;
    $offset = 0;
    $result;
    $t_start = microtime(true);
    while ($size > $offset) {
      foreach ($this->getItemsInterface($offset, $limit)->rows as $row) {
        if ($row->code == $model) {
          dpm(microtime(true) - $t_start, "search timer");
          $this->item = $row;
          return $row;
        }
      }


      $offset += $limit;
    }

    dpm(microtime(true) - $t_start, "search timer");
    return false;  
  }

  public function search($needle)
  {
    $t_start = microtime(true);

    foreach ($this->getItemsInterface(0, 100, $needle)->rows as $row) {
      if ($row->code == $needle) {
          dpm(microtime(true) - $t_start, "new search timer");
          $this->item = $row;
          return $row;
      }
    }
    
  }


  public function getMeta()
  {
    if ($this->item) {
      return $this->item->meta;
    }
  }

  public function getItem(){
    if ($this->item) {
      return $this->item;
    } else return false;
  }
}
