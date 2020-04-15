<?php
include_once 'Config.php';
include_once 'classes/domainobject/DOUser.php';
include_once 'classes/domainobject/DOPollAnswer.php';
include_once 'classes/domainobject/DOPollVote.php';

$ifdUser = new IFDUser();
$clientIP = 0;
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) //Need to check if coming directly from server or proxy server/load balancer forwarded request
    $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
else
    $clientIP = $_SERVER['REMOTE_ADDR'];
$ifdUser->m_IpAddress->SetValue($clientIP);
$doUser = new DOUser();
$doUser->Set($ifdUser);
if ($doUser->Count() == 0) //New user so they can submit to poll
{
    if ($doUser->Save())
    {
        $ifdUser = $doUser->Get();
        $questionId = 0;
        if (isset($_REQUEST['question-id']) && !empty($_REQUEST['question-id']) && is_numeric($_REQUEST['question-id']))
            $questionId = $_REQUEST['question-id'];
        $answerId = 0;
        if (isset($_REQUEST['answer-id']) && !empty($_REQUEST['answer-id']) && is_numeric($_REQUEST['answer-id']))
            $answerId = $_REQUEST['answer-id'];
        $ifdPollAnswer = new IFDPollAnswer();
        $ifdPollAnswer->m_Id->SetValue($answerId);
        $ifdPollAnswer->m_PollQuestionId->SetValue($questionId);
        $doPollAnswer = new DOPollAnswer();
        $doPollAnswer->Set($ifdPollAnswer);
        if ($doPollAnswer->Count() == 1) //Question and answer exists so I can insert into poll_vote
        {
            $ifdPollVote = new IFDPollVote();
            $ifdPollVote->m_PollAnswerId->SetValue($answerId);
            $ifdPollVote->m_UserId->SetValue($ifdUser->m_Id->GetValue());
            $doPollVote = new DOPollVote();
            $doPollVote->Set($ifdPollVote);
            $doPollVote->Save(); //If error in save it will show up in logs
        }
    }
}
echo 'Thank you for your submission';