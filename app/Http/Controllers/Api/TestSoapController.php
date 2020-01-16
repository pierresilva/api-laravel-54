<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\SoapController;
use DateInterval;
use DatePeriod;
use DateTime;
use SimpleXMLElement;
use stdClass;

use GuzzleHttp\Client;

class TestSoapController extends SoapController
{
    //

    private $xmlr;

    private function getCrReservasRequest()
    {
        $xmlr = new SimpleXMLElement("<Request></Request>");
        $xmlr->addAttribute('apikey', config('cr_reservas.apyKey'));
        $xmlr->addAttribute('userName', config('cr_reservas.userName'));
        $xmlr->addAttribute('password', config('cr_reservas.password'));

        return $xmlr;
    }

    private function makeCrReservasRequest()
    {
        $xmlr = $this->xmlr;

        $soapRequest = new stdClass();
        $soapRequest->xml = $xmlr->asXML();

        $client = new Client([
            'base_uri' => config('cr_reservas.url'),
        ]);

        $response = null;

        try {
            $response = $client->post(
                config('cr_reservas.action'),
                [
                    'body'    => $soapRequest->xml,
                    'headers' => [
                        'Content-Type' => 'application/xml',
                    ]
                ]
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $client->getConfig(),
                ]
            ], 400);
        }

        if ($response->getStatusCode() === 200) {
            $xmlResponse = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA);
            
            $response = json_decode(json_encode((array) $xmlResponse, true), true);

            if (isset($response['error'])) {
                return response()->json(json_decode(json_encode((array) $xmlResponse, true)), 400);
            }
            return response()->json(json_decode(json_encode((array) $xmlResponse, true)));
        } else {
            $xmlResponse = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA);
            return response()->json(json_decode(json_encode((array) $xmlResponse, true)), 400);
        }
    }

    /**
     * This method return the list of all hotels associated to an username/password.
     */
    public function getHotels()
    {
        $xmlr = $this->getCrReservasRequest();
        $xmlr->addChild('getHotels');

        $this->xmlr = $xmlr;

        return $this->makeCrReservasRequest();
    }

    /** 
     * It gives the list of rates for a selected hotel.
     */
    public function getRates($hotelId = null)
    {
        $xmlr = $this->getCrReservasRequest();

        $xmlr->addChild('getRates')->addAttribute('hotelId', $hotelId ? $hotelId : config('cr_reservas.userName'));

        $this->xmlr = $xmlr;

        return $this->makeCrReservasRequest();
    }

    /**
     * It gives the list of rooms for a selected hotel. 
     */
    public function getRooms($hotelId = null)
    {
        $xmlr = $this->getCrReservasRequest();

        $xmlr->addChild('getRooms')->addAttribute('hotelId', $hotelId ? $hotelId : config('cr_reservas.userName'));

        $this->xmlr = $xmlr;

        return $this->makeCrReservasRequest();
    }

    /**
     * It gives the list of channels managed by RC for a selected hotel.
     */
    public function getPortals($hotelId = null)
    {
        $xmlr = $this->getCrReservasRequest();

        $xmlr->addChild('getPortals')->addAttribute('hotelId', $hotelId ? $hotelId : config('cr_reservas.userName'));

        $this->xmlr = $xmlr;

        return $this->makeCrReservasRequest();
    }

    /**
     * IThe reservations function let the client retrieve reservations from the RoomCloud local database.
     */
    public function getReservations($startDate = null, $endDate = null, $hotelId = null)
    {
        $sDate = $startDate ? $startDate : date('Y-m-d');
        $eDate = $endDate ? $endDate : date('Y-m-d');
        $xmlr = $this->getCrReservasRequest();

        $xmlr->addChild('reservations')->addAttribute('hotelId', $hotelId ? $hotelId : config('cr_reservas.userName'));
        $reservations = $xmlr->addChild('reservations');
        $reservations->addAttribute('hotelId', $hotelId ? $hotelId : config('cr_reservas.userName'));
        // Get by creation date, omit for by checkin date
        // $reservations->addAttribute('useDLM', 'true');
        $reservations->addAttribute('startDate', $sDate);
        $reservations->addAttribute('endDate', $eDate);

        $this->xmlr = $xmlr;

        return $this->makeCrReservasRequest();
    }

    /**
     * The reservations function let the client retrieve reservations from the RoomCloud local database.
     */
    public function getAvailability($startDate = null, $endDate = null, $hotelId = null)
    {
        $sDate = $startDate ? $startDate : date('Y-m-d');
        $eDate = $endDate ? $endDate : date('Y-m-d');

        $xmlr = $this->getCrReservasRequest();

        $view = $xmlr->addChild('view');
        $view->addAttribute('hotelId', $hotelId ? $hotelId : config('cr_reservas.userName'));
        $view->addAttribute('startDate', $sDate);
        $view->addAttribute('endDate', $eDate);

        $this->xmlr = $xmlr;

        return $this->makeCrReservasRequest();
    }

    public function modifyInventory(Request $request)
    {
        $sDate = $request->startDate ? $request->startDate : date('Y-m-d');
        $eDate = $request->endDate ? $request->endDate : date('Y-m-d');
        $hotel = $request->hotelId ? $request->hotelId : config('cr_reservas.userName');
        $room = $request->roomId;
        $quantity = $request->quantity;

        $xmlr = $this->getCrReservasRequest();

        $modify = $xmlr->addChild('modify');
        $modify->addAttribute('hotelId', $hotel);
        $modify->addAttribute('startDate', $sDate); // Must be today or before, and equal or after end date
        $modify->addAttribute('endDate', $eDate); // Must be today or before, and equal or before start date

        $begin = new DateTime($sDate);
        $end = new DateTime($eDate);
        $end = $end->modify('+1 day');

        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($begin, $interval, $end);

        $previous = null;
        $dates    = [];
        foreach ($daterange as $dt) {
            $current = $dt->format("Y-m-d");
            if (!empty($previous)) {
                $show = new DateTime($current);
                // $dates[] = [$previous, $show->format("Y-m-d")];
                $dates[] = $show->format("Y-m-d");
            } else {
                $dates[] = $dt->format("Y-m-d");
            }
            $previous = $current;
        }

        foreach ($dates as $date) {
            $availability = $modify->addChild('availability');
            $availability->addAttribute('day', $date);
            $availability->addAttribute('roomId', $room);
            $availability->addAttribute('quantity', $quantity);
        }

        $this->xmlr = $xmlr;

        return $this->makeCrReservasRequest();
    }
}
