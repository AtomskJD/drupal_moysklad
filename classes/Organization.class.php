<?php

/**
* Класс организации
*/
class Organization extends Connector
{
  function __construct()
  {
    $this->setEntity("organization");  
  }

  public function getOrganization()
  {
    return $this->getItems(0, 1);
  }

  public function getMeta()
  {
    $tmp = $this->getItems(0, 1);
    return $tmp[0]->meta;
  }
}
