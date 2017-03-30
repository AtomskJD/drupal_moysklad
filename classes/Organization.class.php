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
    return $this->getItems(0, 1)[0]->meta;
  }
}
