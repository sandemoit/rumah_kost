<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Log;

class WhatsAppHelper
{
    public static function sendWhatsApp($target, $message)
    {
        $curl = curl_init();
        $token = env('FONNTE_API_TOKEN');

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('target' => $target, 'message' => $message),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $token,
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new Exception(curl_error($curl));
        }

        curl_close($curl);

        return $response;
    }
    public static function checkWhatsApp($target)
    {
        $curl = curl_init();
        $token = env('FONNTE_API_TOKEN');

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/validate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $target,
                'countryCode' => '62'
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $token,
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new Exception(curl_error($curl));
        }

        $decodedResponse = json_decode($response, true);

        curl_close($curl);

        // Debug response
        Log::info('WhatsApp API Response: ', $decodedResponse);

        if (substr($target, 0, 2) === '08') {
            $nomor_wa = '62' . substr($target, 1);
        } else {
            $nomor_wa = $target;
        }

        if (
            $decodedResponse['not_registered'] || $decodedResponse['registered'] == $nomor_wa && $decodedResponse['status'] == 'true'
        ) {
            return false;
        }

        return true;
    }
}
