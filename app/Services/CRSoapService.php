<?php

namespace App\Services;

use Log;
use SoapClient;

class CRSoapService
{

    private function _client($F5srv)
    {
        $opts = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $context = stream_context_create($opts);
        $wsdl = "https://apitest.roomcloud.net/be/search/xml.jsp";

        try {
            $this->client = new \SoapClient(
                $wsdl,
                [
                    'stream_context' => $context,
                    'trace' => true,
                    'apikey' => 'bamboo_900hty5768fj5o6msds4',
                    'userName' => '3885',
                    'password' => 'Bamboo2019',
                ]
            );

            return $this->client;
        } catch (\Exception $e) {
            Log::info('Caught Exception in client' . $e->getMessage());
        }
    }

    public function getHotels($params)
    {
        $this->client = $this->_client($params['config']);

        try {
            $result = $this->client->getHotels($params);
            return $result;
        } catch (\Exception $e) {
            Log::info('Caught Exception :' . $e->getMessage());
            return $e;       // just re-throw it
        }
    }
}
