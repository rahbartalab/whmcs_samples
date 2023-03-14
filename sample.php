<?php
// Define your WHMCS API credentials
$username = '{WHMCS_USERNAME}';
$password = '{WHMCS_PASSWORD}';

// Define the domain registration information
$firstname = 'John';
$lastname = 'Doe';
$email = 'john.doe@example.com';
$address1 = '123 Main St';
$city = 'Anytown';
$state = 'CA';
$country = 'US';
$zip = '12345';
$phonenumber = '+1.5555551212';
$domain = 'example.com';
$regperiod = 1; // Number of years to register the domain for

// Define the billing information
$cardtype = 'visa';
$cardnumber = '4111111111111111';
$cardexp = '0624'; // Format: MMYY (ex. June 2024)
$cardcvv = '123';

// Set up the API request parameters for domain registration
$domain_params = array(
    'username' => $username,
    'password' => $password,
    'action' => 'AddOrder',
    'clientid' => 0,
    'firstname' => $firstname,
    'lastname' => $lastname,
    'email' => $email,
    'address1' => $address1,
    'city' => $city,
    'state' => $state,
    'country' => $country,
    'postcode' => $zip,
    'phonenumber' => $phonenumber,
    'domain' => array(
        $domain => array(
            'regperiod' => $regperiod,
        ),
    ),
);

// Call the API to register the domain and get the response
require_once '/path/to/whmcs/init.php';
$domain_command = 'AddOrder';
$domain_adminuser = ''; // Set this to your admin username if you're making API requests from an admin account
$domain_response = localAPI($domain_command, $domain_params, $domain_adminuser);

// Get the order ID for the domain registration
if ($domain_response['result'] == 'success') {
    $orderid = $domain_response['orderid'];
    $domainstatus = $domain_response['status'];
} else {
    $error = $domain_response['message'];
}

// Set up the API request parameters for payment automation
$payment_params = array(
    'username' => $username,
    'password' => $password,
    'action' => 'AcceptOrder',
    'orderid' => $orderid,
    'autosetup' => true, // Set this to true to automatically set up hosting or services associated with the order
    'paymentmethod' => 'creditcard',
    'cardtype' => $cardtype,
    'cardnum' => $cardnumber,
    'cardexp' => $cardexp,
    'cardcvv' => $cardcvv,
);

// Call the API to automate payment and get the response
$payment_command = 'AcceptOrder';
$payment_adminuser = ''; // Set this to your admin username if you're making API requests from an admin account
$payment_response = localAPI($payment_command, $payment_params, $payment_adminuser);

// Process the API response
if ($payment_response['result'] == 'success') {
    $invoiceid = $payment_response['invoiceid'];
    $paymentstatus = $payment_response['status'];
    $transid = $payment_response['transid'];
    // The domain registration and payment have been completed successfully
} else {
    $error = $payment_response['message'];
    // There was an error automating the payment
}