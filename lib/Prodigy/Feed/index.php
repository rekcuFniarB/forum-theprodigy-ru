<?php
// Registering lazy services
$this->registerServices(
    array(
        array('feedRender', '\Prodigy\Feed\Render'),
        array('feedData',   '\Prodigy\Feed\DataQuery'),
        array('feedsrvc',       'Prodigy\Feed\Service')
    )
);

//// Defaults for GET requests
$this->respond('GET', null, function($request, $response, $service, $app) {
    if ($response->isSent()) return;
//     $app->srvc->build_menu();
    
    $service->before = intval($request->param('before', null));
    $service->pageNext = 0;
    $service->pagePrev = 0;
    $service->paginateBy = 25;
    $service->next_page_available = false;
    $service->post_view = false;
});

$this->respond('GET', '/test/', 'main->example');


//// Show category
$this->respond('GET', '/[i:cat]/[all:all]?/', 'feedRender->category'); // Category view controller

?>
