<?php

// Registering lazy services
$this->registerServices(
    array(
        array('admin', '\Prodigy\Admin\Admin'),
    )
);

// Show bans list
$this->respond(array('GET', 'POST'), '/bans/', 'admin->bans');
$this->respond(array('GET', 'POST'), '/editnews/', 'admin->editnews');
$this->respond('GET', '/', 'admin->dashboard');
