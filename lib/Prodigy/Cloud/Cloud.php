<?php

namespace Prodigy\Cloud;
use \Prodigy\Respond\Respond;

class Cloud extends Respond
{
    protected $OAuth;
    protected $authCredentials;
    protected $imgTypes;
    protected $conf;
    
    public function __construct($router)
    {
        parent::__construct($router);
        
        if(exec('which convert') == '')
            $this->app->errors->abort('', 'Convert util from ImageMagick not found.', 500);
        
        $this->conf = new \Klein\DataCollection\DataCollection($this->app->conf->cloud);
        
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
            //$conf = $router->app()->conf;
            $authCredentials = new \Google\Auth\Credentials\UserRefreshCredentials($this->conf->scopes, $this->conf->credentials);
            
            $_this->OAuth = $authCredentials->oauth();
            if($_this->OAuth->getAuthorizationUri() === null)
                $_this->OAuth->setAuthorizationUri($this->conf->credentials['auth_uri']);
            if($_this->OAuth->getRedirectUri() === null)
                $_this->OAuth->setRedirectUri($this->conf->credentials['redirect_uris'][0]);
            
            $_this->authCredentials = $authCredentials;
            return $authCredentials;
        });
        
        $this->app->register('GooglePhotosClient', function() use($router) {
            return new \Google\Photos\Library\V1\PhotosLibraryClient(['credentials' => $this->app->authCredentials]);
        });
        
        // extensions and mimes of images
        $this->imgTypes = array(
            'png',
            'image/png',
            'jpg',
            'image/jpeg',
            'jpeg',
            'gif',
            'image/gif',
            'webp',
            'image/webp'
        );
        
        $this->conf->cache_dir = PROJECT_ROOT . "/static/attachments/cloud";
        $this->conf->cache_url = $this->service->protocol . $this->service->host . STATIC_ROOT . '/attachments/cloud';
        $this->conf->tmpdir = sys_get_temp_dir();
    } // __construct()
    
    /**
     * is uploaded file an image?
     * @param array $file uploaded file info ($_FILES)
     * @return bool
     */
    protected function is_image($file)
    {
        if (empty($file['type']))
            return false;
        if (in_array($file['type'], $this->imgTypes))
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
        if ($cnt == 1)
            return '';
        else
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
        $response = $this->app->GooglePhotosClient->batchCreateMediaItems($newMediaItems, ['albumId' => $this->conf->albumID]);
        
        $items = array();
        
        foreach($response->getNewMediaItemResults() as $itemResult)
        {
            $items[] = $itemResult->getMediaItem()->getId();
        }
        return $items;
    }
    
    /**
     * Upload file
     * @param string $path file path
     * @param string $title file title
     * @param array $info file info
     * @return array
     */
    protected function upload_file($path, $title, $info)
    {
        // add json lenght into itself
        $juplinfo = json_encode($info);
        $infolen = strlen($juplinfo);
        $infolenlen = strlen((string)$infolen);
        $infolen = $infolen - 4 + $infolenlen;
        $info['infosize'] = $infolen;
        $juplinfo = json_encode($info);
        
        $patched_file = "$path.tmp";
        file_put_contents($patched_file, $juplinfo);
        exec("cat $path >> $patched_file");
        
        // total size of data
        $size = $infolen + $info['size'];
        
        // Resolution of image
        $resolution = $this->getResolution($size);
        
        // actual length in bytes (width * height * 3 bytes)
        $real_length = $resolution[0] * $resolution[1] * 3;
        
        if ($size < $real_length)
        {
            $fill = random_bytes($real_length - $size);
            error_log("__DEBUG__: Appending zeroes $real_length - $size");
            // fill empty space
            file_put_contents($patched_file, $fill, FILE_APPEND);
        }
        
        rename($patched_file, $path);
        
        $rslt = $this->file2png($path, "$path.png", $resolution);
        
        error_log("__DEBUG__: convert status: $rslt, $path");
        
        if ($rslt)
        {
            $upl_rslt = $this->upload_image(array(
                array(
                    "path" => "$path.png",
                    "title" => $info['title'],
                    "description" => base64_encode($juplinfo)
                )
            ));
            return $upl_rslt;
        }
        else
        {
            return array('__ERROR__');
        }
        
        
    }
    
    /**
     * Count optimal image resolution
     * @param int $size file size in bytes
     * @return array [width, height]
     */
    protected function getResolution($size)
    {
        error_log("__DEBUG__: Resolution from $size");
        
     
        // Small images damaging workaround for Google cloud.
        if ($size < 900)
           $size = 900;
        
        $pixels = intval($size/3 + 1);
        $y = intval(sqrt($pixels));
        $x = intval($pixels/$y) + 1;
        error_log("__DEBUG__: RESOLUTION: $x, $y");
        return array($x, $y);
    }
    
    protected function file2png($finput, $foutput, $resolution, $depth = 8)
    {
        $_finput = escapeshellarg($finput);
        $_foutput = escapeshellarg($foutput);
        $_resolution = escapeshellarg("{$resolution[0]}x{$resolution[1]}");
        $_depth = escapeshellarg($depth);
        $command = "convert -depth $_depth -size $_resolution RGB:$_finput PNG:$_foutput";
        error_log($command);
        $output = exec($command);
        error_log("__DEBUG__: convert output: $output");
        
        return file_exists($foutput) && filesize($foutput) > 0;
    }
    
    protected function png2file($png, $output)
    {
        $tmpfile = tempnam($this->conf->tmpdir, 'PHP');
        
        // convert png to file
        $png = escapeshellarg($png);
        exec("convert $png RGB:$tmpfile");
        
        // get length of json string from file
        $data = file_get_contents($tmpfile, false, NULL, 12, 32);
        $data = explode(',', $data);
        $infosize = (int)$data[0];
        
        // read part of file containing json
        $info = file_get_contents($tmpfile, false, NULL, 0, $infosize);
        $info = json_decode($info, true);
        
        // Copy part of file to new file
        $tf = fopen($tmpfile, 'r');
        $nf = fopen($output, 'w');
        error_log("__DEBUG__: Writing file to $output");
        stream_copy_to_stream($tf, $nf, $info['size'], $info['infosize']);
        fclose($tf);
        fclose($nf);
        unlink($tmpfile);
    }
    
    protected function get_remote_file($url, $name)
    {
        $output = "{$this->conf->cache_dir}/$name";
        
        if (file_exists($output))
            return true;
        
        $opt = stream_context_create(array('http'=>
            array(
                'timeout' => 15,
                'header' => array('User-Agent' => '"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:64.0) Gecko/20100101 Firefox/64.0')
            )
        ));
        
        $png = "{$this->conf->tmpdir}/$name.png";
        
        file_put_contents($png, fopen($url, 'r', false, $opt));
        $this->png2file($png, $output);
        unlink($png);
        
        if (file_exists($output))
            return true;
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
            
            $POST = $request->paramsPost();
            
            $uplfile = $request->files()->get('uplfile');
            $filesize = filesize($uplfile['tmp_name']);
            
            error_log("__UPLOAD__: TYPE: {$uplfile['type']}");
            
            $uplInfo = array(
                "infosize" => null,
                "user" => $app->user->name,
                "realName" => $app->user->realName,
                "title" => $POST->title,
                "description" => $POST->description,
                "name" => $uplfile['name'],
                "type" => $uplfile['type'],
                "ext" => $this->get_file_ext($uplfile['name']),
                "mime" => $uplfile['type'],
                "size" => $filesize
            );
            
            if ($this->is_image($uplfile))
            {
                if ($filesize > $this->conf->piece_size)
                    return $app->errors->abort('', 'Uploaded file size exceeded.', 500);
                
                $uplInfo['type']  = 'image';
                
                $enc_descr = base64_encode(json_encode($uplInfo));
                
                $upl_rslt = $this->upload_image(array(
                    array(
                        "path" => $uplfile['tmp_name'],
                        "title" => $POST->title,
                        "description" => $enc_descr
                    )
                ));
            
            } // if image
            else
            {
                // it's a file, not an image
                $upl_rslt = $this->upload_file($uplfile['tmp_name'], $POST->title, $uplInfo);
            } // not image
            
            return $response->json($upl_rslt);
            
        } // if method POST
        else
        {
            // Show upload form if it's GET request
            $service->title = "Upload files";
            $service->sessid = $app->session->id;
            $this->addCSS('cloud.css');
            $this->render('templates/cloud/upload.template.php');
        } // if method GET
        
    } // main()
    
    public function show($request, $response, $service, $app) {
        $itemID = $request->paramsNamed()->get('id');
        $dl = $request->paramsGet()->get('dl');
        $item = $app->GooglePhotosClient->getMediaItem($itemID);
        $service->BaseUrl = $item->getBaseUrl();
        $HEADERS = $request->headers();
        $accept = $HEADERS->get('accept');
        $_referer = $HEADERS->get('referer');
        $referer = parse_url($_referer);
        $service->sesc = $app->session->id;

        $info = json_decode(base64_decode($item->getDescription()), true);
        
        $charset = $this->app->conf->get('charset', 'UTF-8');
        $info['title'] = mb_convert_encoding($info['title'], $charset, 'UTF-8');
        $info['description'] = mb_convert_encoding($info['description'], $charset, 'UTF-8');
        
        $service->info = $info;
        
        $service->title = "File: {$service->info['title']}";
        $service->uri = $service->siteurl . $request->uri();
        
        if(empty($service->info['ext']))
            $ext = '.'.$this->get_file_ext($service->info['name']);
        else
            $ext = ".{$service->info['ext']}";
        if ($ext == '.')
            $ext = '';
        
        // if request came from embedded link and referer is our site
        if (strpos($accept, 'text/html') === false && $referer['host'] == $service->host)
        {
            if ($service->info['type'] == 'image')
            {
                // Redirect to remote image if it was embedded on our site
                return $response->redirect($service->BaseUrl . '=d')->send();
            }
            else
            {
                // Not an image
                
                $rslt = $this->get_remote_file($service->BaseUrl . '=d', "$itemID$ext");
                if ($rslt)
                    return $response->redirect("{$this->conf->cache_url}/$itemID$ext");
                else
                    return $app->errors->abort('', "Failed to get file $itemID$ext", 400);
            }
        }
        elseif ($referer['path'] == SITE_ROOT . $request->pathname())
        {
            // came from same page from download link
            
            // validate session
            $app->session->check('get');
            
            $e_name = urlencode($info['name']);
            
            $rslt = $this->get_remote_file($service->BaseUrl . '=d', "$itemID$ext");
            if ($rslt)
                return $response->redirect("{$this->conf->cache_url}/$itemID$ext?dl=$e_name");
            else
                return $app->errors->abort('', "Failed to get file $itemID$ext", 400);
        }
        else
        {
            // Just show page with preview
            
            $service->embed_code = false;
            if(!$app->user->guest)
                $service->embed_code = true;
            
            $app->conf->mediaplayer = true;
        
            if(strpos($info['mime'], 'video') !== false)
                $service->type = 'video';
            elseif(strpos($info['mime'], 'audio') !== false)
                $service->type = 'audio';
            elseif(strpos($info['mime'], 'image') !== false)
                $service->type = 'img';
            else
            {
                $app->conf->mediaplayer = false;
                $service->type = null;
                $service->embed_code = false;
            }
            
            $this->addCss('cloud.css');
            return $this->render('templates/cloud/show.template.php');
        }
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
        
        error_log('__RENDER__: iteminfo');
        
        $this->render('templates/cloud/main.template.php');
    } // example()
    
    public function example($request, $response, $service, $app)
    {
        error_log('__RENDER__: example');
        $service->title = 'Cloud examples';
        $img = imagecreatefromstring('qwerty');
        var_dump($img);
        $this->render('templates/cloud/example.template.php');
    }
}
