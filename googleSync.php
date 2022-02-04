<?php 
require __DIR__ . '/vendor/autoload.php'; 

/*function getAuthenticationUrl()
{
    $client = new Google_Client();
    $client->setClientId($this->client_id);
    $client->setClientSecret($this->client_secret);
    $client->setRedirectUri($this->redirect_uri);
    $client->addScope("openid email");
    $authUrl = $client->createAuthUrl();
    return $authUrl;
}
*/



function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Calendar API PHP Quickstart');
    $client->setScopes(Google_Service_Calendar::CALENDAR);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

//#### response.php (esta es la respuesta desde google hacia mi sitio) ####

// Cargue el token previamente autorizado desde un archivo, si existe.
// El archivo token.json almacena los tokens de acceso y actualización del usuario, y es
// creado automáticamente cuando el flujo de autorización se completa por primera vez.

// ¿¿¿¿¿¿¿¿ $tokenPath desde BBDD ???????????
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

// Si no hay token anterior o está caducado.
    if ($client->isAccessTokenExpired()) {
// Actualice el token si es posible, de lo contrario, busque uno nuevo.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
// Solicitar autorización del usuario.
            $authUrl = $client->createAuthUrl();
            //printf("Abre el siguiente link en tu navegador:\n%s\n", $authUrl);
            echo "<a href='".$authUrl."'>Conectar a Google</a>";
            //¿¿¿¿¿¿¿Colocar el authCode en un input ya creado en la vista del programa????????
            $authCode = trim(fgets(STDIN));

// Intercambia el código de autorización por un token de acceso.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

// Verifica si hubo un error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
// ¿¿¿¿¿¿¿¿¿¿¿¿¿¿Guarda el token en la BBDD en la tabla usuario???????????????????
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}




function obtenerGoogleIds(){ 
    $client = getClient();
    $service = new Google_Service_Calendar($client);
    $calendarId = 'primary';
    $events = $service->events->listEvents($calendarId);
    while(true) {
    foreach ($events->getItems() as $event) {
        $eventsId = $event->getId();
        $googleIds[] = $eventsId;
    }  $pageToken = $events->getNextPageToken();
    if ($pageToken) {
      $optParams = array('pageToken' => $pageToken);
      $events = $service->events->listEvents( $calendarId, $optParams);
    } else {
      break;
  }
  }
  return $googleIds;
}
  
function createEventGoogle($event){ 
   
    $client = getClient();
    $calendarId = 'primary';
    $service = new Google_Service_Calendar($client);
    $eventGoogleId = md5(uniqid(rand(), true));
    $event["id"] = $eventGoogleId;
    $event = new Google_Service_Calendar_Event($event);
    $event = $service->events->insert($calendarId, $event); 
    return  $event->getId();
}

function updateEventGoogle($eventId){

    $client = getClient();
    $calendarId = 'primary';
    $service = new Google_Service_Calendar($client);
    $event = $service->events->get($calendarId,$eventId);

    // Actualización de resumen, localización del evento y color de evto
    $event->setSummary('Prueba funcion actualización');
    $event->setLocation('Calle San Pedro 44, 14900 Lucena, Córdoba');
    $event->setColorId(11);

    // Actualización de hora inicio de evento
    $Start = new Google_Service_Calendar_EventDateTime();
    $Start->setDateTime('2022-02-03T15:00:00');
    $Start->setTimeZone('Europe/Madrid');
    $event->setStart($Start); 


    //Actualización de hora final de evento
    $end = new Google_Service_Calendar_EventDateTime();
    $end->setDateTime('2022-02-03T16:00:00');
    $end->setTimeZone('Europe/Madrid');
    $event->setEnd($end);

    //Actualización de participantes en evento  
    $attendees = new Google_Service_Calendar_EventAttendee();
    
    //Actualización de participantes modo individual
    // $attendee1->setEmail('attendeeEmail');
    
    //Actualización de participantes modo colectivo
    $attendees = array(
        array('email' => 'jporteromapfre@gmail.com'),
        array('email' => 'ytrop@hotmail.com'),
    );

    $event->attendees = $attendees;
 
    $updatedEvent = $service->events->update($calendarId, $event->getId(), $event);
    return $updatedEvent; 

  } 

  function deleteEventGoogle($eventId){


  $client = getClient();
  $calendarId = 'primary';
  $service = new Google_Service_Calendar($client);
  $eventdelete = $service->events->delete($calendarId,$eventId);
  return $eventdelete; 

}












?>


