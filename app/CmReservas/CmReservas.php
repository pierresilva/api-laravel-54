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

        if (!is_array($reservations)) {
            $reservations = [
                $reservations
            ];
        }

        foreach ($reservations as $reservation) {
            $reservationJson = json_encode($reservation);
            $reservationAttributes = $reservation->{$attributesKey};
            $reservationRoom = $reservation->room->{$attributesKey};
            $dayPrices = [];
            $res = null;
            // Servicio para obtener cambio de moneda
            $url = 'https://free.currconv.com/api/v7/convert?q=' . $reservationAttributes->currencycode . '_COP&compact=ultra&apiKey=495ed2da7dca68522b41';

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
                select nit, numrec from dathot;
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
            // ToDo: Parametrizar ID forma de pago 'forpag', tipo garantia 'tipgar', tipo programa 'tippro' en config/cm_reservas.php
            $paymentType = config('cm_reservas.paymentType');
            $warrantyType = config('cm_reservas.warrantyType');
            $programType = config('cm_reservas.programType');
            $json_data = json_encode((array)$reservation, true);
            $queryReserva = "
                    INSERT INTO reserva
                    (numres ,referencia ,tipdoc ,cedula, nit, numhab, tipres, codusu, fecres, feclle, fecsal, feclim, numadu, numnin, numinf, observacion, numpre, carta, habfij, solicitada, forpag, fecest, estado, tippro, tipgar, codven, tipseg, metadata)
                    VALUES
                    ('$numres->res','cm-reservas {$reservationAttributes->id}', {$tipDoc->tipdoc},'0','{$nit}','$numhab->numhab','5','1',curdate(),'$reservationAttributes->checkin','$reservationAttributes->checkout' ,'$reservationAttributes->checkin','$reservationAttributes->adults','$reservationAttributes->children','0','$observacion','11','N','N','$reservationAttributes->firstName $reservationAttributes->lastName',{$paymentType},null,'G','{$programType}','{$warrantyType}','1', 'I', '{$reservationJson}');";
            \DB::insert($queryReserva);
            $codpla = config('cm_reservas.codpla');
            $dayPriceCnt = 1;
            foreach ($dayPrices as $dayPrice) {
                $queryPlares = "
                INSERT INTO plares
                (numres, numpla, codpla, fecini, fecfin, pordes, tipdes, valornoche)
                VALUES({$numres->res}, {$dayPriceCnt}, {$codpla}, '{$dayPrice->day}', '{$dayPrice->day}', 0, 'P', {$dayPrice->price});
                ";
                \DB::insert($queryPlares);
                $dayPriceCnt++;
            }
            $queryReccaj = "
            INSERT INTO reccaj
            (numrec, cedula, nombre, direccion, ciudad, telefono, fecha, codcaj, codusu, codcar, codven, nota, estado)
            VALUES({$dathot->numrec}, '0', '{$reservationAttributes->firstName} {$reservationAttributes->lastName}', '{$reservationAttributes->address}', '{$reservationAttributes->city}', '{$reservationAttributes->telephone}', CURRENT_DATE, 1, 1, 54, 0, 'Pago de reserva por cm_reservas.', 'A');
            ";
            \DB::insert($queryReccaj);

            $queryDetrec = "
            INSERT INTO detrec
            (numrec, numero, forpag, numfor, fecven, ivarep, valorm, valor)
            VALUES({$dathot->numrec}, 1, 1, 0, CURRENT_DATE, 0, 0, {$reservationAttributes->price});
            ";
            \DB::insert($queryDetrec);

            $queryGarres = "
            INSERT INTO garres
            (numres, item, codusu, codcaj, fecha, codcar, total, numrec, numegr, estado)
            VALUES({$numres->res}, 1, 1, 3, CURRENT_TIMESTAMP, 54, {$reservationAttributes->price}, {$dathot->numrec}, 0, 'A');
            ";
            \DB::insert($queryGarres);

            $nNumrec = ($dathot->numrec + 1);
            \DB::update("
            UPDATE dathot
            SET
            numrec = {$nNumrec}
            WHERE numrec = {$dathot->numrec};
            ");

            $queryNumfolio = "select MAX(numfol)+1 as fol from folio";

            $numfolio = collect(\DB::select($queryNumfolio))->first();

            $sqlFolio = "
            INSERT INTO folio
            (numfol, numres, codeve, tipdoc, cedula, nit, nitage, locpro, codpai, codciu, paides, locdes, ciudes, codtra, trasal, codmot, numhab, usuout, codusu, fecres, feclle, fecsal, hora, horsal, numadu, numnin, numinf, nota, notaayb, equipaje, placa, trahot, estpai, corregir, forpag, estado, walkin, tippro, tipgar, codven, idresweb, idcanal, idclifre)
            VALUES(
            {$numfolio->fol}, 
            {$numres->res}, 
            0,
            {$tipDoc->tipdoc}, 
            '0',
            '0', 
            '0', 
            127591, 
            null, 
            null, 
            null, 
            129499, 
            null, 
            0, 
            0, 
            null, 
            '$numhab->numhab', 
            null, 
            null, 
            '{$reservationAttributes->dlm}', 
            '{$reservationAttributes->checkin}', 
            '{$reservationAttributes->checkout}', 
            null, 
            null, 
            1, 
            0, 
            0, 
            'FOLIO CREADO PARA GARANTIZAR RESERVA EN LINEA CM_RESERVAS', 
            '', 
            'N', 
            null, 
            'N', 
            null, 
            'N', 
            'N',
            null, 
            'O', 
            'A', 
            '', 
            '', 
            0, 
            null,
            null, 
            null
            );
            ";

            \DB::insert($sqlFolio);

            $queryValcar = "
                INSERT INTO valcar
                (numfol, numcue, item, codusu, codcaj, fecha, cantidad, codcar, cladoc, numdoc, codpla, valor, iva, impo, valser, valter, total, estado, oldfol, movcor)
                VALUES({$numfolio->fol}, 1, 1, 3, 7, CURRENT_TIMESTAMP, 1, 54, 'RC', '2109', null, {$reservationAttributes->price}, 0, null, 0, 0, {$reservationAttributes->price}, 'A', null, 'N');
            ";

            \DB::insert($queryValcar);

            $period = new DatePeriod(
                new DateTime($reservationAttributes->checkin),
                new DateInterval('P1D'),
                new DateTime($reservationAttributes->checkout)
            );

            $datesToCheck = [];

            foreach ($period as $key => $value) {
                $datesToCheck[] = $value->format('Y-m-d');
            }

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
        \DB::setDefaultConnection('hhotel5');
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
                WHERE reserva.feclle < '$reservationAttributes->checkout' AND reserva.fecsal > '$reservationAttributes->checkin'
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
                WHERE reserva.feclle < '$reservationAttributes->checkout' AND reserva.fecsal > '$reservationAttributes->checkin'
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
        // \DB::connection('hhotel5');
        $roomsOccupied = [];

        $roomsBlocked = collect(\DB::connection('hhotel5')->select("
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

        $roomsReserved = collect(\DB::connection('hhotel5')->select("
                SELECT reserva.numres, reserva.numhab, reserva.estado, habitacion.codcla
                FROM reserva
                INNER JOIN habitacion ON reserva.numhab = habitacion.numhab
                WHERE reserva.feclle <= '$out' AND reserva.fecsal > '$in'
                AND reserva.estado IN ('P','G')
                AND habitacion.codcla = {$roomClass}
            "));

        foreach ($roomsReserved as $roomReserved) {
            $roomsOccupied[] = $roomReserved->numhab;
        }

        $roomsHosted = collect(\DB::connection('hhotel5')->select("
                SELECT folio.numres, folio.numhab, folio.estado, habitacion.codcla, folio.numfol
                FROM folio
                INNER JOIN habitacion ON folio.numhab = habitacion.numhab
                WHERE folio.feclle <= '$out' AND folio.fecsal > '$in'
                AND folio.estado IN ('I')
                AND habitacion.codcla = {$roomClass}
            "));

        foreach ($roomsHosted as $roomHosted) {
            $roomsOccupied[] = $roomHosted->numhab;
        }

        $roomsOccupied = implode('\',\'', $roomsOccupied);

        $numhabs = collect(\DB::connection('hhotel5')->select("
            select habitacion.numhab, habitacion.numcam
            from habitacion 
            where habitacion.numhab not in ('{$roomsOccupied}')
            AND habitacion.codcla = {$roomClass}
            "));

        $availability = [
            'rooms' => 0,
            'beds' => 0,
            'occupied' => '\'' . $roomsOccupied . '\'',
            'class' => config('cm_reservas.rooms_lc.' . $roomClass),
        ];

        foreach ($numhabs as $numhab) {
            $availability['rooms'] += 1;
            $availability['beds'] += $numhab->numcam;
        }

        return $availability;
    }

}
