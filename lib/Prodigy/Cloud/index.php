<?php
// Registering lazy services
$this->registerServices(
    array(
        array('files', '\Prodigy\Cloud\Cloud'),
        //array('authCredentials', 'Google\Auth\Credentials\UserRefreshCredentials'),
        //array('GooglePhotosClient', 'Google\Photos\Library\V1\PhotosLibraryClient'),
        //array('PhotosLibraryResourceFactory', 'Google\Photos\Library\V1\PhotosLibraryResourceFactory')
    )
);

// $router = $this;
// $this->app()->register('authCredentials', function() use ($router) {
//     $conf = $router->app()->conf;
//     return new \Google\Auth\Credentials\UserRefreshCredentials($conf->cloud['scopes'], $conf->cloud['credentials']);
// });

error_log("__DEBUG__: WITH CLOUD");

$this->respond(array('GET', 'POST'), '/', 'files->main');
$this->respond('GET', '/show/[:id]/', 'files->show');
$this->respond('GET', '/example/iteminfo/', 'files->iteminfo');
