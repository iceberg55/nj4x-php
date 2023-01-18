<?php

namespace D4T\Nj4x;

use D4T\Nj4x\Nj4xClient;
use D4T\Nj4x\Nj4xTerminalRunResult;
use D4T\Nj4x\Nj4xTerminalRunResultType;

class Nj4xTerminalManager
{

    public static function RunTerminal(string $app, string $endpoint, string $broker_server_name,
        int $account_number, string $password, array $config = []) : Nj4xTerminalRunResult
    {

        try {
            $client = new Nj4xClient($app, $endpoint);

            $config = json_encode($config);

            $ret = $client->run($broker_server_name, $account_number, $password, $config);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Error Fetching http headers') !== false || strpos($e->getMessage(), 'Session expired') !== false) {
                return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED_REPEATABLE, $e->getMessage());
            }

            return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED, $e->getMessage());
        }

        if (is_object($ret)) {
            return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED_REPEATABLE, 'Object returned: ' . serialize($ret));
        }

        if ($ret == 'OK, started' || $ret == 'OK') {
            return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::OK);
        }

        if (strpos($ret, 'Invalid user name or password') !== false) {
            return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED | Nj4xTerminalRunResultType::FAILED_W_ALERT, 'Invalid user name or password');
        }

        if (
            $ret == ''
            || strpos($ret, 'Unexpected error') !== false
            || strpos($ret, 'NOK') !== false
            || strpos($ret, 'Session expired') !== false
            || strpos($ret, 'Error Fetching http headers') !== false
            || strpos($ret, 'SOAP-ERROR: Parsing WSDL') !== false
        ) {
            return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED_REPEATABLE, $ret);
        }

        if (strpos($ret, 'Reached max number of terminals') !== false) {
            return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED_REPEATABLE | Nj4xTerminalRunResultType::FAILED_W_ALERT, $ret);
        }

        if (strpos($ret, 'SRV file not found: com.jfx.ts.net.SrvFileNotFound') !== false) {
            $name = str_replace('SRV file not found: com.jfx.ts.net.SrvFileNotFound:', '', $ret);
            return new Nj4xTerminalRunResult(
                Nj4xTerminalRunResultType::FAILED_W_ALERT | Nj4xTerminalRunResultType::FAILED_REPEATABLE,
                "Broker server file is not found: '$name'"
            );
        }

        return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED, 'Unhandled Error.' . $ret);
    }

    public static function GetHostInfo(string $app, string $endpoint) : mixed
    {
        $client = new Nj4xClient($app, $endpoint);

        return $client->info($app);
    }

    public static function Ping(string $app, string $endpoint) : bool
    {
        try {
            $client = new Nj4xClient($app, $endpoint);
            $client->info();

            return true;
        } catch(\Exception $e) {

        }

        return false;
    }

    public static function StopTerminal(string $app, string $endpoint, string $broker_server_name, int $account_number, string $password) : Nj4xTerminalRunResult
    {
        try {
            $client = new Nj4xClient($app, $endpoint);

            $ret = $client->stop($broker_server_name, $account_number, $password);

            if (
                strpos($ret, 'Session expired') !== false
                || strpos($ret, 'Error Fetching http headers') !== false
            ) {
                return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED_REPEATABLE, $ret);
            }
        } catch (\Exception $e) {
            if (
                strpos($e->getMessage(), 'Error Fetching http headers') !== false
                || strpos($e->getMessage(), 'Session expired') !== false
            ) {
                return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED_REPEATABLE, $e->getMessage());
            }

            return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED, $e->getMessage());
        }

        return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::OK);
    }

    public static function CheckTerminal(string $app, string $endpoint, string $broker_server_name, int $account_number, string $password)
    {

        try {
            $client = new Nj4xClient($app, $endpoint);

            $ret = $client->check($broker_server_name, $account_number, $password);
            if (
                strpos($ret, 'Invalid user name or password') !== false
                || strpos($ret, 'NOK') !== false
            ) {
                return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::ACCOUNT_INVALID, 'Wrong login or password');
            }

            if (
                strpos($ret, 'Session expired') !== false
                || strpos($ret, 'Error Fetching http headers') !== false
                || strpos($ret, 'SOAP-ERROR: Parsing WSDL') !== false
            ) {
                return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED_REPEATABLE, $ret);
            }
        } catch (\Exception $e) {
            if (
                strpos($e->getMessage(), 'Error Fetching http headers') !== false
                || strpos($e->getMessage(), 'Session expired') !== false
                || strpos($e->getMessage(), 'SOAP-ERROR: Parsing WSDL') !== false
            ) {
                return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED_REPEATABLE, $e->getMessage());
            }

            return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::FAILED, $e->getMessage());
        }

        return new Nj4xTerminalRunResult(Nj4xTerminalRunResultType::OK);
    }
}
