<?php
require_once '../../../init.php';
try {
    $results = localAPI('GetTLDPricing', ['currencyid' => '1'], 'admin');
    $tlds = array_map(function ($value) {
        return array_map(function ($value) {
            return [
                'price' => $value,
            ];
        }, $value['register']);
    }, $results['pricing']);


    $response = json_encode($tlds);
    header('Content-Type: application/json'); // set the Content-Type header

    return $response;

} catch (\Exception $exception) {
    logActivity('!error!.module:mihanshop_domain_registration,api,message:' . $exception->getMessage());
}
//
//
//require_once 'init.php';
//
//
//$command = 'CreateOrUpdateTLD';
//$postData = array(
//    'extension' => '.me',
//    'id_protection' => true,
//    'dns_management' => true,
//    'email_forwarding' => true,
//    'epp_required' => true,
//    'currency_code' => 'USD',
//    'grace_period_days' => '0',
//    'grace_period_fee' => '-1',
//    'redemption_period_fee' => '75.00',
//    'register' => array(1 => '19.000', 5 => '59.000'),
//    'renew' => array(1 => '10.00', 2 => '20.00', 3 => '30.00', 4 => '40.00', 5 => '50.00', 6 => '60.00', 7 => '70.00', 8 => '80.00', 9 => '90.00'),
//    'transfer' => array(1 => '10.00'),
//);
//$adminUsername = 'admin'; // Optional for WHMCS 7.2 and later
//
//$results = localAPI($command, $postData, $adminUsername);
//echo '<pre>';
//var_dump($results);
//echo '<pre>';
//exit();
