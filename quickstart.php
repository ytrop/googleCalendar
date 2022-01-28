<?php

require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}
/**
 * Devuelve un cliente API autorizado.
 * @return Google_Client el objeto de cliente autorizado
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Calendar API PHP Quickstart');
    $client->setScopes(Google_Service_Calendar::CALENDAR);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

// Cargue el token previamente autorizado desde un archivo, si existe.
// El archivo token.json almacena los tokens de acceso y actualización del usuario, y es
// creado automáticamente cuando el flujo de autorización se completa por primera vez.
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
            printf("Abre el siguiente link en tu navegador:\n%s\n", $authUrl);
            print 'Introduce el codigo de verificación: ';
            $authCode = trim(fgets(STDIN));

// Intercambia el código de autorización por un token de acceso.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

// Verifica si hubo un error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
// Guarda el token en un archivo.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

// Obtenga el cliente API y construye el objeto de servicio.
    $client = getClient();
    $service = new Google_Service_Calendar($client);

// Imprime los 10 ultimos resultados del calendario con sus ID
    $calendarId = 'primary';
    $optParams = array(
    'maxResults' => 10,
    'orderBy' => 'startTime',
    'singleEvents' => true,
    'timeMin' => date('c'),
    );
    $results = $service->events->listEvents($calendarId, $optParams);
    $events = $results->getItems();

if (empty($events)) {
    print "No existen proximos eventos.\n";
} else {
    print "Proximos eventos:\n";
    foreach ($events as $event) {
//modificar funcion para controlar si existe id y crear o modificar segun existencia. 
        $start = $event->start->dateTime;
        if (empty($start)) {
            $start = $event->start->date;
        }
    printf("%s (%s)\n", $event->getSummary(), $start);
    } 
} 

//Listar todos los id de los eventos del calendarios tanto futuros como pasados
$events = $service->events->listEvents($calendarId);
while(true) {
  foreach ($events->getItems() as $event) {
    $eventsId = $event->getId();
    $listId[]= $eventsId;  
  }
  $pageToken = $events->getNextPageToken();
  if ($pageToken) {
    $optParams = array('pageToken' => $pageToken);
    $events = $service->events->listEvents( $calendarId, $optParams);
  } else {
    break;
  }
}

//Insercion de evento

//Crea un id para la creacion de eventos en el calendario
$idGenerate = md5(uniqid(rand(), true));
//$idGoogle = vsprintf( $idGenerate, str_split(bin2hex(random_bytes(16)), 4) );
//Creacion de evento
$event = new Google_Service_Calendar_Event(array(
    'id'=>$idGenerate,
    'summary' => 'Google prueba id ',
    'location' => 'Calle Francisca Cabello Hoyos 3, pta. 1, of. 2, 14900 Lucena, Córdoba',
    'description' => 'Reunion principal',
    'start' => array(
      'dateTime' => '2022-01-28T09:12:00-10:00',
      'timeZone' => 'Europe/Madrid',
    ),
    'end' => array(
      'dateTime' => '2022-01-29T11:00:00-12:00',
      'timeZone' => 'Europe/Madrid',
    ),
    'attendees' => array(
      array('email' => 'jporteromapfre@gmail.com'),
      array('email' => 'ytrop@hotmail.com'),
    ),
    'reminders' => array(
        'useDefault' => FALSE,
        'overrides' => array(
        array('method' => 'email', 'minutes' => 24 * 60),
        array('method' => 'popup', 'minutes' => 10),
      ),

    ),
  ));
   
    $event = $service->events->insert($calendarId, $event);
    printf('Evento creado con id: %s\n', $event->getId()); 

//Actualización de eventos
    $event = $service->events->get($calendarId,$idGenerate);
    $event->setSummary('Cita actualizada');
    $updatedEvent = $service->events->update($calendarId, $event->getId(), $event);
    echo $updatedEvent->getUpdated(); 
/*  
//Eliminación de eventos 
    $service-> events->delete($calendarId,$idGenerate);*/