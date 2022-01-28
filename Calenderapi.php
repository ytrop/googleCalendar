<?php
require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}
/**
 * Devuelve un cliente API autorizado.
 * @return Google_Client el objeto de cliente autorizado
 */

//Variable event seran los id de los eventos google guardados en BBDD
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
    $calendarId = 'primary';
    
// Listado de Id events Google Calendar Api
    function getEventsID($service, $calendarId){
        $events = $service->events->listEvents( $calendarId); 
            while(true) {
                foreach ($events->getItems() as $event) {
                $eventsId=$event->getId();
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
            return  $listId;
    }

    function calendarapi($service,$calendarId,$idEvents){
        $listId = getEventsID($service,$calendarId); 
            if(isset ($listId)){ 
                foreach ($idEvents as $event) { 
                    if(in_array($event,$listId)){
                        $event = $service->events->get($calendarId, $idEvents);
                        $event->setSummary('Cita actualizada');//modificar set de actualizacion del evento
                        $updatedEvent = $service->events->update($calendarId, $event->getId(), $event);
                        echo $updatedEvent->getUpdated(); 
                       

                    }
                    else{
                        $idGenerate = md5(uniqid(rand(), true));
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

                    }
                   
                }
            }
    }




?>