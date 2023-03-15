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
    echo $response;

} catch (\Exception $exception) {
    logActivity('!error!.module:mihanshop_domain_registration,api,message:' . $exception->getMessage());
}
