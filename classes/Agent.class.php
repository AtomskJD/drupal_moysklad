<?php

/**
* Класс контрагента
*/
class Agent extends Connector
{

  protected $agent      = NULL;
  protected $agent_id   = NULL;
  protected $is_exists  = NULL;

   
   // первым делом ищем существующего контрагента
  function __construct($mail = false)
  {
    $this->setEntity("counterparty");
    if ($mail) {
      $agent_probe = $this->search($mail);
      // $agent_probe = $this->getByMail($mail);
      
      if ($agent_probe) {
        $this->is_exists = true;
        $this->agent = $agent_probe;
      } else $this->is_exists = false;
    } 
  }

  public function is_exists()
  {
    return $this->is_exists;
  }


  public function setAgent($params)
  {
    $body = array(
      "name"        => (string)($params->delivery_first_name . " " . $params->delivery_last_name),
      "email"       => (string)$params->primary_email,
      "phone"       => (string)$params->delivery_phone,
      "legalTitle"  => (string)$params->delivery_company,
      );
    // dpm($body);
    $this->agent = $this->setItemsInterface(json_encode($body));
  }

  public function search($needle)
  {
    foreach ($this->getItemsInterface(0, 100, $needle)->rows as $row) {
        $result[] = $row;
        if ($row->email == $needle) {
          return $row;
        }
      }
      
  }


  public function getByMail($mail)
  {
    $size = $this->getSize();
    // dpm($size);
    $limit = 100;
    $offset = 0;
    $result;
    while ( $size > $offset ) {
      foreach ($this->getItemsInterface($offset, $limit)->rows as $row) {
        $result[] = $row;
        if ($row->email == $mail) {
          return $row;
        }
      }


      $offset += $limit;
    }

    return false;

  }

  public function createNew($params) 
  {
    // $params['mail']
    // $params['name']
    // $params['phone']
  }


  public function getMeta()
  {
    // dpm($this->agent, "agent - meta");
    if ($this->agent) {
      return $this->agent->meta;
    } else return false;
  }

  public function getAgent()
  {
    if ($this->agent) {
      return $this->agent;
    } else return false;
  }


}
