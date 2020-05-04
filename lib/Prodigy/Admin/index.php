<?php

// Registering lazy services
$this->registerServices(
    array(
        array('admin', '\Prodigy\Admin\Admin'),
    )
);

// Show bans list
$this->respond(array('GET', 'POST'), '/bans/', 'admin->bans');
$this->respond(array('GET', 'POST'), '/managecats/', 'admin->manageCats');
$this->respond('POST', '/managecats/create/', 'admin->createCat');
$this->respond(array('GET', 'POST'), '/editnews/', 'admin->editnews');
$this->respond(array('GET', 'POST'), '/editagreement/', 'admin->editagreement');
$this->respond('GET', '/', 'admin->dashboard');
