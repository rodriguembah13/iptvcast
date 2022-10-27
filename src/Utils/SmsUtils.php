<?php

namespace App\Utils;

use Exception;

class SmsUtils
{


    static function sendSms($phone,$message){
        $api_key="QnN4b3JCck55Skp5QmJpRW5Bbkg=";
        $from="PMU AGENSIC";
       $url = 'https://app.techsoft-web-agency.com/sms/api';
        $destination = $phone;
        $sms="Information
            Dans la course ".$message['reunion'].$message['course']." le cheval numero ".$message['cheval']." a retenu notre attention.
            Bonne journée.
            Votre partenaire Hippique Agensic.
            ";
        $sms_body = array(
            'action' => 'send-sms',
            'api_key' => $api_key,
            'to' => $destination,
            'from' => $from,
            'sms' => $sms
        );

        $send_data = http_build_query($sms_body);
        $gateway_url = $url . "?" . $send_data;

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $gateway_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            $output = curl_exec($ch);

            if (curl_errno($ch)) {
                $output = curl_error($ch);
            }
            curl_close($ch);

        }catch (Exception $exception){
            $output= $exception->getMessage();
        }
        return $output;
    }
    static function getSolde(){
        $api_key="QnN4b3JCck55Skp5QmJpRW5Bbkg=";
        $from="PMU AGENSIC";
        $url = 'https://app.techsoft-web-agency.com/sms/api';
        // Construire le corps de la requête
        $sms_body = array(
            'action' => 'check-balance',
            'api_key' => $api_key
        );

        $send_data = http_build_query($sms_body);

        $gateway_url = $url . "?" . $send_data;

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $gateway_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            $output = curl_exec($ch);

            if (curl_errno($ch)) {
                $output = curl_error($ch);
            }
            curl_close($ch);

        }catch (Exception $exception){
            $output= $exception->getMessage();
        }
        return $output;
    }
    static function addUser($data){
        $url = 'http://smsplatform.test/contacts/api';
        $api_key = 'a3l0am5pd3JodWxwQnhCbE9nYkM=';

// Créer le corps de la requete
        $query_body = array(
            'action' => 'subscribe-us',
            'api_key' => $api_key,
            'phone_book' => "Prospect site web", //ATTENTION: la liste doit déja exister dans votre compte sur la plateforme
            'phone_number' => $data['phone'],    //Numéro de téléphone au format international
            'first_name' => $data['firstname'], // Prenom, OPTIONNEL
            'last_name' => $data['lastname'],	// Nom, OPTIONNEL
            'company' => $data['compazny'],	// Entreprise, OPTIONNEL
            'email' => $data['email']	//adresse email, OPTIONNEL
        );

        $send_data = http_build_query($query_body);

        $gateway_url = $url . "?" . $send_data;

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $gateway_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            $output = curl_exec($ch);

            if (curl_errno($ch)) {
                $output = curl_error($ch);
            }
            curl_close($ch);


        }catch (Exception $exception){
            $output= $exception->getMessage();
        }
        return $output;
    }
}