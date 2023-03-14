<?php
// Create by  : Hossein Rahbartalab
// Created at : 2023/03/13

use Illuminate\Database\Capsule\Manager as Capsule;

const DATE_TIME_REGEX = '/^\d{4}-\d{2}-\d{2}\s([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/'; // format : YYYY-MM-DD XX:XX:XX
const TOKEN = '1234';
const TLD_LIST = ['com', 'net', 'org', 'biz', 'info', 'co', 'me', 'club', 'mobi', 'cc', 'tv', 'center', 'company', 'city', 'click', 'vip', 'download', 'ir'];

/* --!> main logic <!-- */
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    /* --!> must send bearer token for authorization <!-- */
    $hasPermission = false;
    if (isset($_SERVER['HTTP_AUTHORIZATION']) && strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer') === 0) {
        if (substr($_SERVER['HTTP_AUTHORIZATION'], 7) === TOKEN) {
            $hasPermission = true;
        }
    }
    if (!$hasPermission) {
        logActivity('|module|:mihanshop_domain_registration,|message|:unauthorized attempt');
        header('location: 403');
    }

    /* --!> main validation <!-- */
    $errors = [];

    $client_id = (isset($_POST['client_id']) && is_numeric($_POST['client_id'])) ? intval($_POST['client_id']) : null;
    if (is_null($client_id)) {
        $errors['client_id'][] = validationMessages('client_id')['required'];
    }

    $expireDate = (isset($_POST['expire_date']) && preg_match(DATE_TIME_REGEX, $_POST['expire_date'])) ? $_POST['expire_date'] : null;
    if (is_null($expireDate)) {
        $errors['expire_date'][] = validationMessages('expire_date')['required'];
    }

    if (empty($errors)) {

        // TODO : register domain

    } else {
        $_SESSION['errors'] = $errors;
    }

}

function validationMessages($name)
{
    return [
        'required' => "the $name is required",
        'integer' => "the $name must be integer",
        'dateTime' => "the $name must be a valid date time"
    ];
}

/* --!> module configuration and outputs <!-- */
const TABLE_NAME = 'mihanshop_domains';
const MODULE_NAME = 'mihanshop_domain_registration';

function mihanshop_domain_registration_config()
{
    /* --!> description & fields can be changed  <!-- */
    return [
        'name' => 'mihanshop_domain_registration',
        'description' => '',
        'version' => '1.0',
        'author' => 'Hossein Rahbartalab',
        'fields' => [],
    ];
}


function mihanshop_domain_registration_activate()
{
    try {
        /* --!>
         in order we don't drop the tables in deactivate function 
        tables can exist , so we need check before create
         <!-- */
        if (!Capsule::schema()->hasTable(TABLE_NAME)) {
            Capsule::schema()->create(
                TABLE_NAME,
                function ($table) {
                    $table->increments('id');
                    $table->integer('client_id');
                }
            );
        }
        return [
            'status' => 'success',
            'description' => MODULE_NAME . ' activated successfully'
        ];
    } catch (\Exception $exception) {
        logActivity('|module|:mihanshop_domain_registration,|function|:activate,|message|:' . $exception->getMessage());
        return [
            'status' => 'error',
            'description' => 'an error occurred when try to activate ' . MODULE_NAME . ' module'
        ];
    }
}

function mihanshop_domain_registration_deactivate()
{
    /* --!>
    in this section we can drop tables
    but in this case we don't drop they,
    so we don't need "try & catch" structures
    <!-- */

    Capsule::schema()->dropIfExists(TABLE_NAME); // just for testing

    return [
        'status' => 'error',
        'description' => MODULE_NAME . ' deactivate successfully'
    ];
}

function mihanshop_domain_registration_output()
{
    require_once __DIR__ . '/templates/AdminAreaOutput.php';
}
