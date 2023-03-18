<?php
require_once '../../../init.php';

// we can also check for authentication
try {

    $results = localAPI('GetTLDPricing', ['currencyid' => '1'], 'admin');

    $tlds = array_map(function ($tld, $name) {
        return [
            'name' => $name,
            'price' => $tld['register']
        ];
    }, $results['pricing'], array_keys($results['pricing']));

    header('Content-Type: application/json');
    echo json_encode($tlds);
    exit();

} catch (\Exception $exception) {
    logActivity('!error!.module:mihanshop_domain_registration,api,message:' . $exception->getMessage());
}