<?php

namespace D4T\Nj4x;

use SoapClient;

class Nj4xClient
{
  protected $soap;

  public function __construct(string $endpoint) {
    $url_wsdl = 'http://' . $endpoint . ':7789/nj4x/ts?wsdl';
    $this->soap = new SoapClient($url_wsdl, array('location' => $url_wsdl, 'cache_wsdl' => WSDL_CACHE_NONE));
  }

  public function stop($srv, $account_number, $password) : string {

    $token = $this->start_session($account_number);

    $array = array(
      'token' => $token,
      'mt4Account' => array(
        'srv' => $srv,
        'user' => $account_number,
        'password' => $password
      ),
      'nj4xEAParams' => array(
        'historyPeriod' => '10000',
        'period' => '1',
        'strategy' => 'jfx',
        'jfxHost' => '',
        'jfxPort' => 7788,
        'asynchOrdersOperations' => 'false'
      )
    );

    $ret = $this->soap->killMT4Terminal($array)->return;

    $this->soap->close($array);

    return $ret;
  }

  public function info() : array
  {
    $token = $this->start_session('info');

    $array = array(
      'token' => $token
    );

    $ret = $ret = $this->soap->getTSInfo($array);

    $this->soap->close($array);

    return (array)$ret->return;
  }

  public function run($srv, $account_number, $password, $config) : string
  {
    $token = $this->start_session($account_number);

    $set = str_replace('.', '_', $srv);

    $array = array(
      'token' => $token,
      'mt4Account' => array(
        'proxyServer' => '0' . PHP_EOL . 'MarketWatch=../../sets/' . $set . '.sym.txt' . PHP_EOL . 'ProxyEnable=false' . PHP_EOL,
        'proxyType' => 0,
        'srv' => $srv,
        'user' => $account_number,
        'password' => $password
      ),
      'nj4xEAParams' => array(
        'historyPeriod' => '10000',
        'period' => '1',
        'strategy' => $config,
        'jfxHost' => '',
        'jfxPort' => 7788,
        'asynchOrdersOperations' => 'false'
      ),
      'restartTerminalIfRunning' => 'true'
    );

    $ret = $this->soap->runMT4Terminal($array);

    if (isset($ret->return))
      $ret = $ret->return;

    $this->soap->close($array);

    return $ret;
  }

  public function check($srv, $account_number, $password) : string
  {
    $token = $this->start_session($account_number);

    $array = array(
      'token' => $token,
      'mt4Account' => array(
        'srv' => $srv,
        'user' => $account_number,
        'password' => $password
      ),
      'nj4xEAParams' => array(
        'historyPeriod' => '10000',
        'period' => '1',
        'strategy' => 'jfx',
        'jfxHost' => '',
        'jfxPort' => 7788,
        'asynchOrdersOperations' => 'false'
      )
    );

    $ret = $this->soap->checkMT4Terminal($array)->return;

    $this->soap->close($array);

    return $ret;
  }


  private function start_session($id) : string
  {
    $array = array(
      'clientInfo' => [
        'clientName' => 'fxs-' . $id,
        'apiVersion' => '2.6.6'
      ]
    );

    return $this->soap->startSession($array)->return;
  }
}
