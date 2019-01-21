<?php

namespace Prodigy\Cloud;
use \Prodigy\Respond\Respond;

class Cloud extends Respond
{
    protected $scopes;
    protected $OAuth;
    protected $authCredentials;
    protected $credentials;
    protected $albumID;
    protected $imgTupes;
    
    public function __construct($router)
    {
        parent::__construct($router);
        
        $this->albumID = $this->app->conf->cloud['albumID'];
        
        // Registering lazy services
        $router->registerServices(
            array(
                //array('files', '\Prodigy\Cloud\Cloud'),
                //array('authCredentials', 'Google\Auth\Credentials\UserRefreshCredentials'),
                //array('GooglePhotosClient', 'Google\Photos\Library\V1\PhotosLibraryClient'),
                array('PhotosLibraryResourceFactory', '\Google\Photos\Library\V1\PhotosLibraryResourceFactory')
            )
        );

        $_this = $this;
        $this->app->register('authCredentials', function() use ($router, $_this) {
            $conf = $router->app()->conf;
            $authCredentials = new \Google\Auth\Credentials\UserRefreshCredentials($conf->cloud['scopes'], $conf->cloud['credentials']);
            
            $_this->OAuth = $authCredentials->oauth();
            if($_this->OAuth->getAuthorizationUri() === null)
                $_this->OAuth->setAuthorizationUri($this->credentials['auth_uri']);
            if($_this->OAuth->getRedirectUri() === null)
                $_this->OAuth->setRedirectUri($this->credentials['redirect_uris'][0]);
            
            $_this->authCredentials = $authCredentials;
            return $authCredentials;
        });
        
        $this->app->register('GooglePhotosClient', function() use($router) {
            return new \Google\Photos\Library\V1\PhotosLibraryClient(['credentials' => $this->app->authCredentials]);
        });
        
        $this->credentials = $this->app->conf->cloud['credentials'];
        $this->scopes = $this->app->conf->cloud['scopes'];
        
        $this->imgTypes = array(
            'png',
            'jpg',
            'jpeg',
            'gif',
            'webp'
        );
    } // __construct()
    
    protected function is_image($imgname)
    {
        $ext = $this->get_file_ext($imgname);
        if (in_array($ext, $this->imgTypes))
            return true;
        else
            return false;
    }
    
    /**
     * Get file extension
     * @param string $filename - file name
     * @return string extension
     */
    protected function get_file_ext($filename)
    {
        $_filename = explode('.', $filename);
        $cnt = sizeof($_filename);
        return strtolower($_filename[$cnt - 1]);
    }
    
    /**
     * Store access token for reusing later
     */
    protected function storeAccessToken()
    {
        // 
    }
    
    /**
     * Uploads image to Google Photo server
     * @param array $images array of images to upload.
     *     Array should contain arrays with "path", "title" and "description" for each image.     
     * @return mixed returns array of itemIDs or FALSE
     */
    protected function upload_image($images)
    {
        $newMediaItems = array();
        
        foreach($images as $image)
        {
            $uploadToken = $this->app->GooglePhotosClient->upload(file_get_contents($image['path']), $image['title']);
            $newMediaItems[] = $this->app->PhotosLibraryResourceFactory::newMediaItemWithDescription($uploadToken, $image['description']);
        }
        $response = $this->app->GooglePhotosClient->batchCreateMediaItems($newMediaItems, ['albumId' => $this->albumID]);
        
        $items = array();
        
        foreach($response->getNewMediaItemResults() as $itemResult)
        {
            $items[] = $itemResult->getMediaItem()->getId();
        }
        return $items;
    }
    
    // URL /main/
    public function main($request, $response, $service, $app)
    {
        if ($app->user->posts < 100)
        {
            return $app->errors->abort('Forbidden', "You don't have permission to access here.", 403);
        }
        
        if($request->method() == 'POST')
        {
            $app->session->check('post');
            $uplfile = $request->files()->get('uplfile');
            if ($this->is_image($uplfile['name']))
            {
                $uplfile['IS_IMAGE'] = true;
                // $this->upload_image();
                //$imgpath = $uplfile['tmp_name'] . '-' . $uplfile['name'];
                //$mvrs = move_uploaded_file($uplfile['tmp_name'], $imgpath);
                //if (!$mvrs)
                    //return $app->erros->abort('', 'Upload preparing failed.', 500);
                
                $POST = $request->paramsPost();
                
                $uplInfo = array(
                    "user" => $app->user->name,
                    "realName" => $app->user->realName,
                    "title" => $POST->title,
                    "description" => $POST->description,
                    "name" => $uplfile['name'],
                    "type" => 'image'
                );
                
               $enc_descr = base64_encode(json_encode($uplInfo));
               
               $upl_rslt = $this->upload_image(array(
                   array(
                       "path" => $uplfile['tmp_name'],
                       "title" => $POST->title,
                       "description" => $enc_descr
                   )
               ));
               
               return $response->json($upl_rslt);
               
            } // if image
            
            
            //return $response->json($uplfile);
        } // if method POST
        else
        {
            $service->title = "Upload files";
            $service->sessid = $app->session->id;
            $this->addCSS('cloud.css');
            $this->render('templates/cloud/upload.template.php');
        } // if method GET
        
    } // main()
    
    public function show($request, $response, $service, $app) {
        $itemID = $request->paramsNamed()->get('id');
        $item = $app->GooglePhotosClient->getMediaItem($itemID);
        $service->BaseUrl = $item->getBaseUrl();
        $HEADERS = $request->headers();
        $accept = $HEADERS->get('accept');
        $referer = parse_url($HEADERS->get('referer'));
        
        // Redirect to remote image if it was embedded on our site
        if (strpos($accept, 'text/html') === false && $referer['host'] == $service->host)
            return $response->redirect($service->BaseUrl . '=d')->send();
        
        $service->info = json_decode(base64_decode($item->getDescription()), true);
        $service->title = "File: {$service->info['title']}";
        $service->uri = $request->uri();
        
        $service->embed_code = false;
        if(!$app->user->guest)
            $service->embed_code = true;
        
        $this->addCss('cloud.css');
        return $this->render('templates/cloud/show.template.php');
    }
    
    // URL /example/
    public function iteminfo($request, $responce, $service, $app)
    {
        $service->title = "Uploaded file info";
        
        //$photosLibraryClient = new PhotosLibraryClient(['credentials' => $authCredentials]);
        
        // $item = $app->PhotosLibraryResourceFactory::newMediaItemWithDescription('$uploadToken', '$itemDescription'); // Ok, it works
        
        $itemID = 'AN-pzIW3qNgLPR_8jf3z2xKE_gnsknWnr5QZUDB9cdWIvKUSt-p7Vi7J1l7Qr24mWoGYLjVK2Fs3quodpYWjMliD2AcBdLbl_Q';
        
        $item = $app->GooglePhotosClient->getMediaItem($itemID);
        
        $service->itemID = $item->getId();
        $service->description = $item->getDescription();
        $service->BaseUrl = $item->getBaseUrl();
        $service->ProdUrl = $item->getProductUrl();
        $service->Filename = $item->getFilename();
        
        $this->render('templates/cloud/main.template.php');
    } // example()
}