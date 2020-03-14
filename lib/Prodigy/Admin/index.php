<?php

// Registering lazy services
$this->registerServices(
    array(
        array('admin', '\Prodigy\Admin\Admin'),
    )
);

// Show bans list
$this->respond('GET', '/bans/', 'admin->bans');
