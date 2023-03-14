<?php

// Created by Hamed Haghi and Fatemeh Babaei
// Created at 2022-09-06

use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\Classes\Custom\Sanitize;
use WHMCS\Classes\Custom\Helpers;

//require_once HELPERS_CLASS;
//require_once SANITIZE_CLASS;

add_hook('AdminAreaViewTicketPage', 1, function ($vars) {

//    $_GET = Sanitize::clear($_GET);

//    $ticketId = (isset($vars['ticketid']) && is_numeric($vars['ticketid'])) ? (int)Sanitize::clear($vars['ticketid']) : null;
    $ticketId = $vars['ticketid'];
    if ($ticketId !== null && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

        //region current admin
        $adminId = (isset($_SESSION['adminid']) && is_numeric($_SESSION['adminid'])) ? (int)Sanitize::clear($_SESSION['adminid']) : null;
        if ($adminId === null) {
            Helpers::echoJSON([
                'success' => false,
                'message' => 'شناسه مدیر یافت نشد',
            ]);
        }
        $admin = Capsule::table('tbladmins')->where('id', $adminId)->first();
        if (empty($admin)) {
            Helpers::echoJSON([
                'success' => false,
                'message' => 'مدیر یافت نشد',
            ]);
        }
        //endregion

        //region validation
//        $_POST = Sanitize::clear($_POST);

        $predefinedReplyId = (isset($_POST['predefinedReplyId']) && is_numeric($_POST['predefinedReplyId'])) ? (int)$_POST['predefinedReplyId'] : null;
        $statusId = (isset($_POST['statusId']) && is_numeric($_POST['statusId'])) ? (int)$_POST['statusId'] : null;

        if ($predefinedReplyId === null) {
            Helpers::echoJSON([
                'success' => false,
                'message' => 'متنی را انتخاب نمایید',
            ]);
        }

        $predefinedReply = Capsule::table('tblticketpredefinedreplies')->where('catid', 1)->where('id', $predefinedReplyId)->first();

        if (empty($predefinedReply)) {
            Helpers::echoJSON([
                'success' => false,
                'message' => 'متن انتخابی یافت نشد',
            ]);
        }

        if (trim($predefinedReply->reply) === '') {
            Helpers::echoJSON([
                'success' => false,
                'message' => 'محتوای متن انتخابی خالی می باشد',
            ]);
        }

        if ($statusId === null) {
            Helpers::echoJSON([
                'success' => false,
                'message' => 'پوشه ای را انتخاب نمایید',
            ]);
        }

        $status = Capsule::table('tblticketstatuses')->where('id', $statusId)->first();
        if (empty($status)) {
            Helpers::echoJSON([
                'success' => false,
                'message' => 'پوشه انتخاب شده یافت نشد',
            ]);
        }

        $ticket = Capsule::table('tbltickets')->where('id', $ticketId)->first();
        if (empty($ticket)) {
            Helpers::echoJSON([
                'success' => false,
                'message' => 'تیکت یافت نشد',
            ]);
        }
        //endregion

        try {

            Capsule::connection()->beginTransaction();

            //region create reply
            $addTicketReply = localAPI('AddTicketReply', array(
                'ticketid' => $ticket->id,
                'message' => $predefinedReply->reply,
                'adminusername' => $admin->firstname . ' ' . $admin->lastname,
                'noemail' => true,
            ), Helpers::$adminUsername);
            //endregion

            if (!isset($addTicketReply['result']) || $addTicketReply['result'] !== 'success') {
                Helpers::echoJSON([
                    'success' => false,
                    'message' => 'خطایی در ثبت ارجاع تیکت رخ داده است ، لطفا دوباره تلاش نمایید',
                ]);
            }

            Capsule::table('tbltickets')->where('id', $ticketId)->update([
                'status' => $status->title,
            ]);

            Capsule::table('tblticketlog')->insert(array(
                'date' => date('Y-m-d H:i:s'),
                'tid' => $ticket->id,
                'action' => "Status changed to $status->title ( by $admin->username )"
            ));

            Capsule::connection()->commit();

            Helpers::echoJSON([
                'success' => true,
                'message' => 'ثبت ارجاع با موفقیت انجام و تا چند ثانیه دیگر به صفحه ی تیکت ها منتقل خواهید شد',
                'redirect_to' => Helpers::$adminAreaLink . 'supporttickets.php',
            ]);

        } catch (Exception $exception) {
            Capsule::connection()->rollBack();
            logActivity('Error on creating ticket reference : ' . $exception->getMessage(), $ticket->userid);
        }

        Helpers::echoJSON([
            'success' => false,
            'message' => 'خطایی در ثبت ارجاع تیکت رخ داده است',
        ]);
    }
});

add_hook('AdminAreaFooterOutput', 1, function () {

//    $_GET = Sanitize::clear($_GET);

    if (isset($_GET['action'], $_GET['id']) && ($_GET['action'] === 'view' || $_GET['action'] === 'viewticket') && basename($_SERVER['SCRIPT_NAME']) === 'supporttickets.php' && trim($_GET['id']) !== '') {

        $ticketId = (int)$_GET['id'];

        $baseURL = "admin/" . "supporttickets.php?action=view&id={$ticketId}";

        $title = 'ارجاع تیکت با شناسه : ' . $ticketId;

        $predefinedReplies = Capsule::table('tblticketpredefinedreplies')->where('catid', 1)->latest('id')->get();
        $predefinedOptions = '';
        if (!empty($predefinedReplies)) {
            $predefinedOptions .= '<option value="" data-reply="">انتخاب کنید</option>';
            foreach ($predefinedReplies as $predefinedReply) {
                $predefinedOptions .= "<option value='{$predefinedReply->id}' data-reply='{$predefinedReply->reply}'>{$predefinedReply->name}</option>" . PHP_EOL;
            }
        }

        $ticketStatuses = Capsule::table('tblticketstatuses')->get();
        $statusOptions = '';
        if (!empty($ticketStatuses)) {
            $statusOptions .= '<option value="" data-reply="">انتخاب کنید</option>';
            foreach ($ticketStatuses as $ticketStatus) {
                $statusOptions .= "<option value='{$ticketStatus->id}'>{$ticketStatus->title}</option>" . PHP_EOL;
            }
        }

        return <<<HTML
            <!-- Modal -->
            <style>label.error{color: #ff4141;font-size: 13px;font-weight: normal; margin-top: 4px;}</style>
            <div class="modal fade" id="ticket-reference-modal" tabindex="-1" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">{$title}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <form id="frm-ticket-reference">
                  <div class="modal-body">
                    <div id="ticket-alert"></div>
                    <div class="form-group">
                        <label for="predefinedReplyId">انتخاب متن :</label>
                        <select name="predefinedReplyId" id="predefinedReplyId" required class="form-control">{$predefinedOptions}</select>
                    </div>
                     <div class="form-group">
                        <label for="message">محتوای متن انتخابی :</label>
                        <textarea name="message" id="message" readonly rows="7" class="form-control"></textarea>
                    </div>
                     <div class="form-group">
                        <label for="statusId">پوشه :</label>
                        <select name="statusId" id="statusId" class="form-control">{$statusOptions}</select>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" onclick="sendTicketReference()" name="submit" class="btn btn-success reference-btn" style="margin-right: 5px">ثبت ارجاع</button>
                  </div>
                  </form>
                </div>
              </div>
            </div>
      
            <script src="../templates/mwh/js/jquery.validate.min.js"></script>
            <script type="text/javascript"> 
            function sendTicketReference() {
              $("#frm-ticket-reference").validate({
                rules: {
                    message: {
                        required: true,
                    },
                    predefinedReplyId: {
                        required: true,
                    },
                    statusId: {
                        required: true,
                    },
                },
                messages: {
                    message: 'محتوای متن انتخابی خالی می باشد',
                    predefinedReplyId: 'متنی را انتخاب نمایید',
                    statusId: 'پوشه را انتخاب کنید',
                },
                submitHandler: function () {
                   $(".reference-btn").prop('disabled', true);
                   const form = $('#frm-ticket-reference *').not('#message').serialize();
                   $.ajax({
                    type: 'POST',
                    url: "$baseURL",
                    data: form,
                    dataType: 'JSON',
                    beforeSend: function() {
                       $('.reference-btn').text('در حال ثبت ...').attr('disabled' , true);
                    },
                    success: function(data) {
                       const alert_type = data.success ? 'alert-success' : 'alert-danger'; 
                       $('#ticket-alert').html('<div class="alert ' + alert_type + '" role="alert">' +
                                 data.message +
                                '<button type="button" class="close">' + 
                                '</button></div>');
                       
                       if(data.success) {
                           $('.reference-btn').text('در حال انتقال ...');
                           setTimeout(function () {
                                window.location.href = data.redirect_to;                               
                           }, 2000);
                       } else {
                           $('.reference-btn').text('ثبت ارجاع').attr('disabled' , false);
                       }
                    },
                    error: function(xhr) {
                       $('.reference-btn').text('ثبت ارجاع').attr('disabled' , false);
                    },
                    complete: function() {
                       
                    },
                });
                }
             }) 
            }
             $(document).ready(function () {
                $('#predefinedReplyId').on('change', function (){
                   $('#message').val($('option:selected', this).attr('data-reply'));
                });
            });
            </script>
HTML;
    }
});