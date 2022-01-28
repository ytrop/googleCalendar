<?php 
/**
  * @param string $calendarId
  * @param string $summary
  * @param \DateTimeImmutable $start
  * @param \DateTimeImmutable $end
  * @param null|string $description
  * @return string Event ID in calendar
  */
    function insertEvent($calendarId, $summary, \DateTimeImmutable $start, \DateTimeImmutable $end, $description = null){
      $event = new \Google_Service_Calendar_Event();
      $event->setSummary($summary);
      $eventStart = new \Google_Service_Calendar_EventDateTime();
      $eventStart->setDateTime($start->format('c'));
      $event->setStart($eventStart);
      $eventEnd = new \Google_Service_Calendar_EventDateTime();
      $eventEnd->setDateTime($end->format('c'));
      $event->setEnd($eventEnd);
      $event->setDescription($description);
      $createdEvent = $this->service->events->insert($calendarId, $event);
      return $createdEvent->id;
    }
    /**
  * A method to edit an existing event in the google calendar
  * @param string $eventId the id of the event to be edited
  * @param string $datetimeStart the starting datetime
  * @param string $datetimeEnd the ending datetime
  * @param null $recurrence whether it should reoccur
  * @param string $timeZone
  * @return mixed the updated event id
  */
  function editEventInCalendar($eventId, $datetimeStart = "0000-00-00T00:00:00", $datetimeEnd = "0000-00-00T00:00:00", $recurrence = null, $timeZone = "Europe/Madrid"){
     # si se utliza un evento recurrente
     if ($recurrence) {
         $recurrence = 'RRULE:FREQ=WEEKLY;COUNT=' . $recurrence;
     } else {
         $recurrence = array();
     }
     # Get the calendar service
     $service = new Google_Service_Calendar($this->client);
     # Get the existing event from the google calendar
     $event = $service->events->get($this->getUserEmail(), $eventId);
     # Set the starting datetime
     $eventDatetimeStart = new Google_Service_Calendar_EventDateTime();
     $eventDatetimeStart->setDateTime($datetimeStart);
     $eventDatetimeStart->setTimeZone($timeZone);
     $event->setStart($eventDatetimeStart);
     # Set the ending datetime
     $eventDatetimeEnd = new Google_Service_Calendar_EventDateTime();
     $eventDatetimeEnd->setDateTime($datetimeEnd);
     $eventDatetimeEnd->setTimeZone($timeZone);
     $event->setEnd($eventDatetimeEnd);
     # Set whether it should reoccur
     $event->setRecurrence(array($recurrence));
     # Update the event
     $updatedEvent = $service->events->update($this->getUserEmail(), $event->getId(), $event);
     # Return the updated ID
     return $updatedEvent->id;
    }

function create_guid(){
    $microTime = microtime();
    list($a_dec, $a_sec) = explode(" ", $microTime);
    $dec_hex = dechex($a_dec* 1000000);
    $sec_hex = dechex($a_sec);
    ensure_length($dec_hex, 5);
    ensure_length($sec_hex, 6);
    $guid = "";
    $guid .= $dec_hex;
    $guid .= create_guid_section(3);
    $guid .= '-';
    $guid .= create_guid_section(4);
    $guid .= '-';
    $guid .= create_guid_section(4);
    $guid .= '-';
    $guid .= create_guid_section(4);
    $guid .= '-';
    $guid .= $sec_hex;
    $guid .= create_guid_section(6);
    return $guid;
}
function create_guid_section($characters){
    $return = "";
    for($i=0; $i<$characters; $i++)
    {
    $return .= dechex(mt_rand(0,15));
    }
    return $return;
}

function ensure_length(&$string, $length){  
  $strlen = strlen($string);  
   if($strlen < $length){  
        $string = str_pad($string,$length,"0");  
    }  
    else if($strlen > $length){  
      $string = substr($string, 0, $length);  
  }  

}  

?>