<?php

namespace App\Http\Controllers;

use Artisaninweb\SoapWrapper\SoapWrapper;
use App\Soap\Request\GetConversionAmount;
use App\Soap\Response\GetConversionAmountResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class SoapTestController extends Controller
{
    //

    /**
     * @var SoapWrapper
     */
    protected $client;

    /**
     * SoapController constructor.
     *
     * @param SoapWrapper $soapWrapper
     */
    public function __construct()
    {

    }

    /**
     * Use the SoapWrapper
     */
    public function show()
    {
        $this->soapWrapper->add('Currency', function ($service) {
            $service
                ->wsdl('http://currencyconverter.kowabunga.net/converter.asmx?WSDL')
                ->trace(true)
                ->classmap([
                    GetConversionAmount::class,
                    GetConversionAmountResponse::class,
                ]);
        });

        // Without classmap
        $response = $this->soapWrapper->call('Currency.GetConversionAmount', [
            'CurrencyFrom' => 'USD',
            'CurrencyTo' => 'EUR',
            'RateDate' => '2014-06-05',
            'Amount' => '1000',
        ]);

        var_dump($response);

        // With classmap
        $response = $this->soapWrapper->call('Currency.GetConversionAmount', [
            new GetConversionAmount('USD', 'EUR', '2014-06-05', '1000')
        ]);

        var_dump($response);
        exit;
    }

    public function soapRequest()
    {
        $requestTimeoutMs = 60000;
        $connectTimeouttMs = 5000;
        $hubUser = "myHubUser";  //Provided by XML Travelgate
        $hubPassword = "myHubPwd"; //Provided by XML Travelgate
        $providerCode = "XXX"; //Provided by XML Travelgate
        $providerAgencyCode = "myAgency";  //Provided by XML Travelgate
        $providerUser = "myProviderUser"; //Provided by Provider
        $providerPassword = "myProviderPwd"; //Provided by Provider
        $providerUrlGeneric = "http://www.myurl.com/bookingservices.asmx"; //Provided by Provider
        $providerParamValueUrlEstaticos = "http://www.myurl.com/syncroservices.asmx"; //Provided by Provider
        $providerParamValueUrlInfoHoteles = "http://www.myurl.com/wshoteles/Service.asmx"; //Provided by

        $url = "http://hubhotelbatch.xmltravelgate.com/Service/Travel/v2/HotelBatch.svc";

        $xmlRQStr = '<OTA_PingRQ Version="0" xmlns="http://www.opentravel.org/OTA/2003/05">
                      <EchoData>RGBrige client is calling</EchoData>
                    </OTA_PingRQ>';
//        $xmlRQStr = "<HotelListRQ xmlns:xsi = \"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd = \"http://www.w3.org/2001/XMLSchema\">"
//            ."<timeoutMilliseconds>".($requestTimeoutMs-1000)."</timeoutMilliseconds>"
//            ."<source>"
//            ." <agencyCode>".$providerAgencyCode."</agencyCode>"
//            ."<languageCode>es</languageCode>"
//            ."</source>"
//            ."<filterAuditData>"
//            ."<registerTransactions>false</registerTransactions>"
//            ."</filterAuditData>"
//            ."<Configuration>"
//            ."<User>".$providerUser."</User>"
//            ."<Password>".$providerPassword."</Password>"
//            ."<UrlGeneric>".$providerUrlGeneric."</UrlGeneric>"
//            ."<Parameters>"
//            ."<Parameter key=\"urlEstaticos\" value=\"".$providerParamValueUrlEstaticos."\"/>"
//            ."<Parameter key=\"urlInfoHoteles\" value=\"".$providerParamValueUrlInfoHoteles."\"/>"
//            ."</Parameters>"
//            ."</Configuration>"
//            ."</HotelListRQ>";
        // echo print_r($xmlRQStr, true);
        // die();
        // Create the Security header
        $ns_s = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
        $node1 = new \SoapVar($hubUser, XSD_STRING, null, null, 'Username', $ns_s);
        $node2 = new \SoapVar($hubPassword, XSD_STRING, null, null, 'Password', $ns_s);
        $token = new \SoapVar(array($node1, $node2), SOAP_ENC_OBJECT, null, null, 'UsernameToken', $ns_s);
        $security = new \SoapVar(array($token), SOAP_ENC_OBJECT, null, null, 'Security', $ns_s);
        $header[] = new \SOAPHeader($ns_s, 'Security', $security, false);

        //Create SOAP Body Request
        $ns_xtg = "http://schemas.xmltravelgate.com/hub/2012/06";
        $nodeTimeoutMilliseconds = new \SoapVar($requestTimeoutMs, XSD_STRING, null, null, "timeoutMilliseconds", $ns_xtg);
        $nodeVersion = new \SoapVar(1, XSD_STRING, null, null, "version", $ns_xtg);
        $nodeCode = new \SoapVar($providerCode, XSD_STRING, null, null, "code", $ns_xtg);
        $nodeId = new \SoapVar(1, XSD_STRING, null, null, "id", $ns_xtg);
        $rqXMLSOAP = new \SoapVar($xmlRQStr, XSD_ANYXML);
        $nodeRqXML = new \SoapVar(array($rqXMLSOAP), SOAP_ENC_OBJECT, null, null, "rqXML", $ns_xtg);
        $nodeProviderRQ = new \SoapVar(array($nodeCode, $nodeId, $nodeRqXML), SOAP_ENC_OBJECT, XSD_STRING, null, "providerRQ", $ns_xtg);
        $hotelListRQ[] = new \SoapVar(array($nodeTimeoutMilliseconds, $nodeVersion, $nodeProviderRQ), SOAP_ENC_OBJECT, null, null, "hotelListRQ", $ns_xtg);

        // Create the SoapClient instance
        $client = new \SoapClient(null, array("trace" => true,
            "exception" => 0,
            "location" => $url,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            "connection_timeout" => $requestTimeoutMs,
            "uri" => $ns_xtg,
            'use' => SOAP_LITERAL));


        // Call
        $result = $client->__soapCall("HotelList", $hotelListRQ,
            array("soapaction" => "http://schemas.xmltravelgate.com/hub/2012/06/IServiceHotelBatch/HotelList"), $header);



        dd($client->__getLastRequest());

        //Print the result
        echo print_r($result, true);
    }

    public function other()
    {
        $wsdl = "https://rzhospicert.rategain.com/rgbridgeapi/ari/receive";
        $client = new \SoapClient(null , [
            'trace' => true,
            'location' => $wsdl,
            'uri' => 'https://rzbetal4.rategain.com/RZIntegration/Services/ARI/RGBR/ARI.SVC',
            'Username' => 'jefe.revenue@germanmoraleshoteles.com',
            'Password' => 'gemo!d@pa7t'
        ]);

        try {

            $xml = '<OTA_PingRQ Version="0" xmlns="http://www.opentravel.org/OTA/2003/05">
                      <EchoData>RGBrige client is calling</EchoData>
                    </OTA_PingRQ>';

            $args = [
                'Username' => 'jefe.revenue@germanmoraleshoteles.com',
                'Password' => 'gemo!d@pa7t'
            ]; //
            //
            $args = array(new \SoapVar($xml, XSD_ANYXML));
            $res = $client->__soapCall('OTA_PingRQ', $args);
            return $res;
        } catch (\SoapFault $e) {
            echo "<hr>Error:";
            echo "<pre>" . $e . "</pre>";
        }

        echo "<hr>Last Request";
        echo "<pre>", htmlspecialchars($client->__getLastRequest()), "</pre>";
    }

    public function anOther()
    {
        $requestXML = '<OTA_PingRQ Version="0" xmlns="http://www.opentravel.org/OTA/2003/05">
                          <EchoData>RGBrige client is calling</EchoData>
                        </OTA_PingRQ>';
        $server = 'https://rzhospicert.rategain.com/rgbridgeapi/ari/receive';
        $headers = [
            "Content-type: text/xml",
            "Content-length: " . strlen($requestXML), "Connection: close",
        ];
        $username = 'jefe.revenue@germanmoraleshoteles.com';
        $password = 'gemo!d@pa7t';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $server);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestXML);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            print curl_error($ch);
            echo "Algo fallo";
        } else {
            echo $data;
            curl_close($ch);
        }
    }

    public function testing()
    {
        $xml = '<OTA_HotelAvailNotifRQ xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" EchoToken="20915" xmlns:xsd="http://www.w3.org/2001/XMLSchema" TimeStamp="2013-09-03T21:38:49" Target="Production" Version="1.0" xmlns="http://www.opentravel.org/OTA/2003/05">
      <AvailStatusMessages HotelCode="20915" HotelName="RGBRTEST">
        <AvailStatusMessage BookingLimitMessageType="SetLimit" BookingLimit="1" xmlns="http://www.opentravel.org/OTA/2003/05">
          <StatusApplicationControl Start="2013-09-04" End="2013-10-31" RatePlanCode="STD" PromotionVendorCode="" InvCode="BAR" Mon="true" Tue="true" Weds="true" Thur="true" Fri="true" Sat="true" Sun="true" />
          <LengthsOfStay>
            <LengthOfStay Time="1" TimeUnit="Day" MinMaxMessageType="SetMinLOS" />
          </LengthsOfStay>
          <UniqueID Type="16" ID="151024" />
          <RestrictionStatus Status="Open" />
        </AvailStatusMessage>
      </AvailStatusMessages>
    </OTA_HotelAvailNotifRQ>';

        $args = new \SoapVar( $xml, XSD_ANYXML );
        $client = new \SoapClient(null, [
            'location' => 'https://rzbetal4.rategain.com/RZIntegration/Services/ARI/RGBR/ARI.SVC',
            'uri' => 'https://rzhospicert.rategain.com/rgbridgeapi/ari/receive'
        ]); // 'https://rzhospicert.rategain.com/rgbridgeapi/ari/receive');
        $results = $client->OTA_HotelAvailNotifRQ( $args );
        // $res = $client->__doRequest($xml, 'https://rzhospicert.rategain.com/rgbridgeapi/ari/receive', 'OTA_HotelAvailNotifRQ', '1.1');

        echo $results;

        echo $client->__getLastRequest();
    }

    public function getSoapClient()
    {
        return $this->client;
    }

    /**
     * Get most recent XML Request sent to SOAP server
     *
     * @return string
     */
    public function getLastRequestXml()
    {
        return $this->client->__getLastRequest();
    }

    /**
     * Get most recent XML Response returned from SOAP server
     *
     * @return string
     */
    public function getLastResponseXml()
    {
        return $this->client->__getLastResponse();
    }

}
