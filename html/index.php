<?php
include_once 'Config.php';
include_once 'classes/domainobject/DOUser.php';

$ifdUser = new IFDUser();
$clientIP = 0;
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) //Need to check if coming directly from server or proxy server/load balancer forwarded request
    $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
else
    $clientIP = $_SERVER['REMOTE_ADDR'];
$ifdUser->m_IpAddress->SetValue($clientIP);
$doUser = new DOUser();
$doUser->Set($ifdUser);
if ($doUser->Count() == 1) //User already submitted to poll
    die(header('Location: /PollResult.php'));
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>IndexMedia Poll</title>
        <link href="/css/poll.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <form id="form1" action="/PollResult.php" method="POST">
        <input type="hidden" id="question-id" name="question-id" value="1" />
        <div class="poll" id="poll-1">
            <div class="poll-content">
                <div class="element-container">
                    <label class="label" id="element-radio-label"><span class="label-value">Which option do you want to select?</span></label>
                    <div class="element-set" id="element-radio-set">
                        <div class="element-content">
                            <div class="option-set">
                                <div class="option-content element-radio-option-content">
                                    <input type="radio" class="form-value" name="answer-id" id="answer-id" value="1">
                                    <label for="answer-id">Option1</label>
                                </div>
                                <div class="option-content element-radio-option-content">
                                    <input type="radio" class="form-value" name="answer-id" id="answer-id" value="2">
                                    <label for="answer-id">Option2</label>
                                </div>
                                <div class="option-content element-radio-option-content">
                                    <input type="radio" class="form-value" name="answer-id" id="answer-id" value="3">
                                    <label for="answer-id">Option3</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="element-container">
                    <div class="element-set" id="element-submit-set">
                        <div class="element-content">
                            <input type="submit" class="submit" name="element-submit" id="element-submit" value="Submit">
                        </div>
                    </div>
                </div>
            </div><!-- poll-content -->
        </div><!-- poll -->
        </form>
    </body>
</html>