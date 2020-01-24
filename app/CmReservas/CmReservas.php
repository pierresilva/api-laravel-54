<?php

namespace App\CmReservas;

use Illuminate\Http\Request;
use DateInterval;
use DatePeriod;
use DateTime;
use SimpleXMLElement;
use stdClass;

use GuzzleHttp\Client;

class CmReservas
{
    private $xmlr;

    public $currencyValue;

    /**
     * This method return the list of all hotels associated to an username/password.
     */
    public function getHotels()
    {
        $xmlr = $this->request();
        $xmlr->addChild('getHotels');

        $this->xmlr = $xmlr;

        return $this->makeRequest();
    }

    /**
     * It gives the list of rates for a selected hotel.
     */
    public function getRates($hotelId = null)
    {
        $xmlr = $this->request();

        $xmlr->addChild('getRates')->addAttribute('hotelId', $hotelId ? $hotelId : config('cm_reservas.userName'));

        $this->xmlr = $xmlr;

        return $this->makeRequest();
    }

    /**
     * It gives the list of rooms for a selected hotel.
     */
    public function getRooms($hotelId = null)
    {
        $xmlr = $this->request();

        $xmlr->addChild('getRooms')->addAttribute('hotelId', $hotelId ? $hotelId : config('cm_reservas.userName'));

        $this->xmlr = $xmlr;

        return $this->makeRequest();
    }

    /**
     * It gives the list of channels managed by RC for a selected hotel.
     */
    public function getPortals($hotelId = null)
    {
        $xmlr = $this->request();

        $xmlr->addChild('getPortals')->addAttribute('hotelId', $hotelId ? $hotelId : config('cm_reservas.userName'));

        $this->xmlr = $xmlr;

        return $this->makeRequest();
    }

    /**
     * IThe reservations function let the client retrieve reservations from the RoomCloud local database.
     */
    public function getReservations($startDate = null, $endDate = null, $hotelId = null, $dlm = false)
    {
        $sDate = $startDate ? $startDate : date('Y-m-d');
        $eDate = $endDate ? $endDate : date('Y-m-d');
        $xmlr = $this->request();

        $xmlr->addChild('reservations')
            ->addAttribute('hotelId', $hotelId ? $hotelId : config('cm_reservas.userName'));
        $reservations = $xmlr->addChild('reservations');
        $reservations->addAttribute('hotelId', $hotelId ? $hotelId : config('cm_reservas.userName'));

        if ($dlm) {
            $reservations->addAttribute('useDLM', 'true');
        }

        $reservations->addAttribute('startDate', $sDate);
        $reservations->addAttribute('endDate', $eDate);


        $this->xmlr = $xmlr;

        return $this->makeRequest();
    }

    /**
     * The reservations function let the client retrieve reservations from the RoomCloud local database.
     */
    public function getAvailability($startDate = null, $endDate = null, $hotelId = null)
    {
        $sDate = $startDate ? $startDate : date('Y-m-d');
        $eDate = $endDate ? $endDate : date('Y-m-d');

        $xmlr = $this->request();

        $view = $xmlr->addChild('view');
        $view->addAttribute('hotelId', $hotelId ? $hotelId : config('cm_reservas.userName'));
        $view->addAttribute('startDate', $sDate);
        $view->addAttribute('endDate', $eDate);

        $this->xmlr = $xmlr;

        return $this->makeRequest();
    }

    public function modifyInventory($startDate = null, $endDate = null, $roomId = null, $hotelId = null, $quantity = null)
    {
        $hotelId = $hotelId ? $hotelId : config('cm_reservas.hotel_id');
        $sDate = $startDate ? $startDate : date('Y-m-d');
        $eDate = $endDate ? $endDate : date('Y-m-d');
        $hotel = $hotelId ? $hotelId : config('cm_reservas.userName');
        $room = $roomId;
        $quantity = $quantity;

        $xmlr = $this->request();

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
        $dates = [];
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

        return $this->makeRequest();
    }

    private function request()
    {
        $xmlr = new SimpleXMLElement("<Request></Request>");
        $xmlr->addAttribute('apikey', config('cm_reservas.apyKey'));
        $xmlr->addAttribute('userName', config('cm_reservas.userName'));
        $xmlr->addAttribute('password', config('cm_reservas.password'));

        return $xmlr;
    }

    private function makeRequest()
    {
        $xmlr = $this->xmlr;

        $soapRequest = new stdClass();
        $soapRequest->xml = $xmlr->asXML();

        $client = new Client([
            'base_uri' => config('cm_reservas.url'),
        ]);

        $response = null;

        try {
            $response = $client->post(
                config('cm_reservas.action'),
                [
                    'body' => $soapRequest->xml,
                    'headers' => [
                        'Content-Type' => 'application/xml',
                    ]
                ]
            );
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }

        if ($response->getStatusCode() === 200) {
            $xmlResponse = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA);

            $response = json_decode(json_encode((array)$xmlResponse, true), true);

            if (isset($response['error'])) {
                return [
                    'error' => true,
                    'message' => 'KO',
                    'data' => json_decode(json_encode((array)$xmlResponse, true)),
                ];
            }
            return [
                'error' => false,
                'message' => 'OK',
                'data' => json_decode(json_encode((array)$xmlResponse, true))
            ];
        } else {
            $xmlResponse = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA);
            return [
                'error' => true,
                'message' => 'KO',
                'data' => json_decode(json_encode((array)$xmlResponse, true)),
            ];
        }
    }

    public function saveReservations($reservations)
    {
        \DB::setDefaultConnection('hhotel5');
        $reservations = $reservations['data']->reservation;
        $attributesKey = '@attributes';

        $tipDoc = collect(\DB::select("select * from tipdoc where detalle = 'CEDULA CIUDADANIA'"))->first();

        foreach ($reservations as $reservation) {
            $reservationAttributes = $reservation->{$attributesKey};
            $reservationRoom = $reservation->room->{$attributesKey};
            $dayPrices = [];
            $res = null;
            $url = 'https://free.currconv.com/api/v7/convert?q=' . $reservationAttributes->currencycode . '_COP&compact=ultra&apiKey=495ed2da7dca68522b41';
            $client = new \GuzzleHttp\Client();
            $request = new \GuzzleHttp\Psr7\Request('GET', $url);

            /*if ($reservationRoom->status != 4) {
                break;
            }*/

            if (isset($reservation->dayPrice)) {
                foreach ($reservation->dayPrice as $dayPrice) {
                    $dayPrices[] = $dayPrice->{$attributesKey};
                }
            }

            $roomClasses = config('cm_reservas.rooms_cl');

            if (!isset($roomClasses[$reservationRoom->id])) {
                break;
            }

            $roomClass = config('cm_reservas.rooms_cl')[$reservationRoom->id];

            $numres = collect(\DB::select('select MAX(numres)+1 as res from reserva limit 1'))->first();

            $dathot = collect(\DB::select("
                select nit from dathot;
            "))->first();

            $reservationExists = collect(\DB::select("
                select numres, referencia from reserva where referencia = 'cm-reservas {$reservationAttributes->id}'
            "))->first();

            if ($reservationExists) {
                break;
            }

            $nit = explode('.', $dathot->nit);
            $nit = implode('', $nit);
            $nit = explode('-', $nit);
            $nit = $nit[0];

            $numhab = $this->getBambooAvailability($reservationAttributes, $roomClass);

            if (!$numhab) {
                break;
            }

            $observacion = "
                cm-reservas {$reservationAttributes->id}
                $reservationAttributes->firstName $reservationAttributes->lastName
                $reservationAttributes->telephone $reservationAttributes->city $reservationAttributes->country
            ";
            // ToDo: Parametrizar ID forma de pago 'forpag', tipo garantia 'tipgar', tipo programa 'tippro'
            $paymentType = config('cm_reservas.paymentType');
            $warrantyType = config('cm_reservas.warrantyType');
            $programType = config('cm_reservas.programType');
            $json_data = json_encode((array) $reservation, true);
            $query = "insert into reserva
                (numres ,referencia ,tipdoc ,cedula, nit, numhab, tipres, codusu, fecres, feclle, fecsal, feclim, numadu, numnin, numinf, observacion, numpre, carta, habfij, solicitada, forpag, fecest, estado, tippro, tipgar, codven, tipseg, metadata)
                values
                ('$numres->res','cm-reservas {$reservationAttributes->id}', {$tipDoc->tipdoc},'0','{$nit}','$numhab->numhab','5','1',curdate(),'$reservationAttributes->checkin','$reservationAttributes->checkout' ,'$reservationAttributes->checkin','$reservationAttributes->adults','$reservationAttributes->children','0','$observacion','11','N','N','$reservationAttributes->firstName $reservationAttributes->lastName',{$paymentType},null,'G','{$programType}','{$warrantyType}','1', 'I', null);";

            $period = new DatePeriod(
                new DateTime($reservationAttributes->checkin),
                new DateInterval('P1D'),
                new DateTime($reservationAttributes->checkout)
            );

            $datesToCheck = [];

            foreach ($period as $key => $value) {
                $datesToCheck[] = $value->format('Y-m-d');
            }

            \DB::beginTransaction();
            try {
                \DB::insert($query);
            } catch (\Exception $exception) {
                \DB::rollBack();
                dd($exception->getMessage(), $exception->getFile(), $exception->getLine());
                return [
                    'error' => true,
                    'message' => $exception->getMessage(),
                    'data' => [
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'previous' => $exception->getPrevious(),
                        'trace' => $exception->getTraceAsString(),
                    ]
                ];
            }
            \DB::commit();
            $promise = $client->sendAsync($request)->then(function ($response) use ($reservationAttributes) {
                $this->currencyValue = $response->getBody();
                $pesos = json_decode( $response->getBody() )->{$reservationAttributes->currencycode . '_COP'};

                \DB::update();
            });
            $promise->wait();
            $availabilities = [];
            foreach ($datesToCheck as $dateToCheck) {
                $availability = $this->getBambooQuantityAvailability($dateToCheck, $dateToCheck, $roomClass);
                $availability['date'] = $dateToCheck;
                $availabilities[] = $availability;
            }
            $modifies = [];
            foreach ($availabilities as $availability) {
                $modifies[] = $this->modifyInventory($availability['date'], $availability['date'], $availability['class'], null, $availability['rooms']);
            }
        }
    }


    public function getBambooAvailability($reservationAttributes, $roomClass)
    {
        $roomsOccupied = [];

        $roomsBlocked = collect(\DB::select("
                SELECT blohab.numhab
                FROM blohab
                INNER JOIN habitacion ON blohab.numhab = habitacion.numhab
                WHERE blohab.fecini <= '$reservationAttributes->checkout' AND blohab.fecfin >= '$reservationAttributes->checkin'
                AND blohab.fecdes IS NULL
                AND habitacion.codcla = {$roomClass}
            "));

        foreach ($roomsBlocked as $roomBlocked) {
            $roomsOccupied[] = $roomBlocked->numhab;
        }

        $roomsReserved = collect(\DB::select("
                SELECT reserva.numres, reserva.numhab, reserva.estado, habitacion.codcla
                FROM `reserva`
                INNER JOIN habitacion ON reserva.numhab = habitacion.numhab
                WHERE reserva.feclle <= '$reservationAttributes->checkout' AND reserva.fecsal >= '$reservationAttributes->checkin'
                AND reserva.estado IN ('P','G')
                AND habitacion.codcla = {$roomClass}
            "));

        foreach ($roomsReserved as $roomReserved) {
            $roomsOccupied[] = $roomReserved->numhab;
        }

        $roomsHosted = collect(\DB::select("
                SELECT reserva.numres, reserva.numhab, reserva.estado, habitacion.codcla, folio.numfol, folio.estado
                FROM `reserva`
                INNER JOIN habitacion ON reserva.numhab = habitacion.numhab
                INNER JOIN folio ON reserva.numhab = folio.numres
                WHERE reserva.feclle <= '$reservationAttributes->checkout' AND reserva.fecsal >= '$reservationAttributes->checkin'
                AND reserva.estado IN ('H')
                AND folio.estado IN ('I')
                AND habitacion.codcla = {$roomClass}
            "));

        foreach ($roomsHosted as $roomHosted) {
            $roomsOccupied[] = $roomHosted->numhab;
        }

        $roomsOccupied = implode('\',\'', $roomsOccupied);

        $numhab = collect(\DB::select("
            select habitacion.numhab 
            from habitacion 
            where habitacion.numhab not in ('{$roomsOccupied}')
            AND habitacion.codcla = {$roomClass}
            "))->first();

        return $numhab;
    }

    /**
     * @return mixed
     */
    public function getBambooQuantityAvailability($in, $out, $roomClass)
    {
        $roomsOccupied = [];

        $roomsBlocked = collect(\DB::select("
                SELECT blohab.numhab
                FROM blohab
                INNER JOIN habitacion ON blohab.numhab = habitacion.numhab
                WHERE blohab.fecini <= '$out' AND blohab.fecfin >= '$in'
                AND blohab.fecdes IS NULL
                AND habitacion.codcla = {$roomClass}
            "));

        foreach ($roomsBlocked as $roomBlocked) {
            $roomsOccupied[] = $roomBlocked->numhab;
        }

        $roomsReserved = collect(\DB::select("
                SELECT reserva.numres, reserva.numhab, reserva.estado, habitacion.codcla
                FROM `reserva`
                INNER JOIN habitacion ON reserva.numhab = habitacion.numhab
                WHERE reserva.feclle <= '$out' AND reserva.fecsal >= '$in'
                AND reserva.estado IN ('P','G')
                AND habitacion.codcla = {$roomClass}
            "));

        foreach ($roomsReserved as $roomReserved) {
            $roomsOccupied[] = $roomReserved->numhab;
        }

        $roomsHosted = collect(\DB::select("
                SELECT reserva.numres, reserva.numhab, reserva.estado, habitacion.codcla, folio.numfol, folio.estado
                FROM `reserva`
                INNER JOIN habitacion ON reserva.numhab = habitacion.numhab
                INNER JOIN folio ON reserva.numhab = folio.numres
                WHERE reserva.feclle <= '$out' AND reserva.fecsal >= '$in'
                AND reserva.estado IN ('H')
                AND folio.estado IN ('I')
                AND habitacion.codcla = {$roomClass}
            "));

        foreach ($roomsHosted as $roomHosted) {
            $roomsOccupied[] = $roomHosted->numhab;
        }

        $roomsOccupied = implode('\',\'', $roomsOccupied);

        $numhabs = collect(\DB::select("
            select habitacion.numhab, habitacion.numcam
            from habitacion 
            where habitacion.numhab not in ('{$roomsOccupied}')
            AND habitacion.codcla = {$roomClass}
            "));

        $availability = [
            'rooms' => 0,
            'beds' => 0,
            'class' => config('cm_reservas.rooms_lc.' . $roomClass),
        ];

        foreach ($numhabs as $numhab) {
            $availability['rooms'] += 1;
            $availability['beds'] += $numhab->numcam;
        }

        return $availability;
    }

}
