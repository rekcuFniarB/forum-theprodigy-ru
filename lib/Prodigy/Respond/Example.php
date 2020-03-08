<?php
namespace Prodigy\Respond;

// Example respond class for test playground
class Example extends Respond
{
    private $yytemplate;
    
    public function __invoke($name) {
        // wasn't invoked :(
        $this->app->errors->log("__DEBUG__: __INVOKE MAIN $name");
        $this->service->flash("__FLASH__: INVOKE MAIN $name");
    }
    
    public function display($name = 'none') {
        $this->app->errors->log("__DEBUG__: display($name)");
        var_dump($this);
    }
    
    protected function test_modify($request)
    {
        $GET = $request->paramsGet();
        $GET->test = 'QWERTY';
    }
    
    public function testResponse($request, $response, $service, $app) {
        $agent = $request->headers()->get('user-agent', 'NONE');
        $ajax = $request->headers()->get('X_REQUESTED_WITH', false);
        $type = $request->param('requesttype');
        $msg = "$agent - $ajax - $type";
        $response->header('X-TEST-x', 'testResponse; OK');
        
        $response->cookie('x-test-cookie', 'testResponse', time() + 60*60*60);
        //setcookie('TEST', 'aaaa', time() + 60*60*60);
        $service->test_cookie = $request->cookies()->get('TEST');
        
        $service->sample = array(
            "k1"=> "v1",
            "k2" => array(
                "k21" => array(
                    "k211" => "v211",
                    "usr" => $app->user
                )
            )
        );
        
        $service->sample2 = 'SAMPLE2';
        
        //$service->usr = $app->user;
        
        //error_log("__TEST__: ". $service->getval('sample2'));
        
        $testval = 'UUUU';
        
        //var_dump($testval); exit();
        
        $msg = "$msg\n<br>TEST: $testval XXX";
        
        //$app->errors->log($msg);
        
        $service->title = 'Bad BB Code test';
        
        $this->test_modify($request);
        
        $service->msg = $request->paramsGet()->get('test');
        
        // Original bad message
        //$service->badmsg = '[size=3]Дорогие друзья!!![/size]<br /><br />Счастлив Вам сообщить, что по многочисленным просьбам трудящихся [b]СДЕЛАНЫ 3 НОВЫЕ КНОПКИ[/b] в форме ответа а именно:<br />[code][video][/video][/code]<br />[code][audio][/audio][/code]<br />[code][hidden][/hidden][/code]<br /><br />Обратите внимание - у кнопок пока что нет &quot;Лиц&quot;... (если у вас в браузере не работает подсветка ссылок, то расположены они именно в таком порядке - В,А,Х, а находятся во втором ряду формы после кнопки &quot;картинка&quot;).<br />[img]http://img2.pict.com/92/3d/af/3412380/0/1272140362.jpg[/img]<br /><br />А это значит, что от Вас зависит, как они будут выглядеть.<br /><br />Скины на форуме разные, кнопки в них отичаются, посему поступим так:<br />Выкладывайте свои наборы из 3-х новых кнопок сюда с указанием СКИНА и с миниголосованием.<br /><br />Удачи!';
        
        /// This is bad too
        //$service->badmsg = '[code][video][/video][/code]
        //[code][audio][/audio][/code]
        //[code][hidden][/hidden][/code]';
        
        //$service->badmsg = '[code][video][/video][/code]';
        
        $service->badmsg = '[code]dick[/code]
        "';
        
//         $file = $line = '';
//         if (headers_sent($file, $line))
//             error_log("__HEADERS_SENT__: $file, $line");
//         else
//             error_log('__HEADERS_SENT__: NO');
        
        //return $this->ajax_response('["dick"]');
        //return $this->error('testing', 400);
        
//         // testing prepared statements
//         $dbst = $app->db->prepare("SELECT memberName FROM members WHERE ID_MEMBER > 100 AND ID_MEMBER < 110
//             AND ? LIKE 'dick'");
//         $dbst->execute(array('dicka'));
//         $found = $dbst->fetchColumn();
//         //var_dump($found);
//         $service->found = $found['memberName'];
//         $emul_mode = $app->db->getAttribute(\PDO::ATTR_EMULATE_PREPARES);
//         $dbst2 = $app->db->prepare("SELECT emailAddress FROM members WHERE ID_MEMBER = 182");
//         $dbst2->execute();
//         $email = $dbst2->fetchColumn();
//         $dbst = null;
//         $dbst2 = null;
//         
//         $buffered = $app->db->getAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);
//         
//         $placeholders = $app->db->build_placeholders(array('dick' => 'DICK', 'ass' => 'ASS', 'cunt' => 'CUNT'), true, false);
//         error_log("__DEBUG__: PLACEHOLDERS: $placeholders");
//         $service->msg = "Emulation mode: $emul_mode, Email: $email, Buffered: $buffered, Placeholders: ($placeholders)";
        //$app->im->send_notice(array(182, 1, 2, 3), '__TEST__', 'Привет бля');
        //$mail_result = $app->im->sendmail(array('retratserif@gmail.com'), 'Subject blah', 'Message blah', null, true);
        //$app->errors->log('__DEBUG__: MAIL: RESULT: ' . var_export($mail_result, true));
        //return $app->errors->abort('TEST', $msg, 200);
        return $this->render('examples/example.php');
        //return $this->message('Message Title', 'Message text');
        //return $this->error($app->locale->txt[1]);
    }
    
    public function example($request, $response, $service, $app) {
        $service->title = 'Example page';
        
        $service->hello = 'Hello';
        
        //$service->load_bbcode();
        
        //$service->validateParam('test', 'Param should be int')->isInt();
        
//         $service->message = "<pre>
//           parent: {$app->respond->instance_id}
//           Board:  {$app->board->instance_id}
//           Main:   {$app->main->instance_id}
//           IM:     {$app->im->instance_id}
//           Cal:    {$app->calendar->instance_id}
//           
//         </pre>";
        $service->values = array('a', 'b', 'c', 'd', 'e');
        
        //var_dump($request->paramsGet()->get('qwerty'));
        
        //return $app->errors->abort('Test', 'Testing ...');
        var_dump($app->locale->get('txt.errors'));
        //throw new \Prodigy\Errors\TemplateException('Example Template Exception', 1);
        
        $this->render('examples/example.php');
        
        //$app->router->skipRemaining();
        
    }
    
    public function example2($request, $response, $service, $app) {
        error_log("__RESPONSE__: EXAMPLE 2 running too.");
        
        $service->title = 'Example page';
        
        $service->hello = 'Hello';
        
        //$this->render('example.php');
    }
    
    public function simple_example($request, $response, $service, $app)
    {
        //$response->chunked = true;
        error_log("__DEBUG__: Chunked: {$response->chunked}, ob_level: " . ob_get_level() . ", ob_length: " . ob_get_length());
        $service->msg = 'This is simple example msg';
        $response->cookie('x-test-cookie', 'simple-example');
        $response->header('x-test', 'simple-example');
        $service->layout('examples/simple.layout.php');
        error_log("__DEBUG__: Chunked: {$response->chunked}, ob_level: " . ob_get_level() . ", ob_length: " . ob_get_length());
        return $this->ajax_response('["qwerty"]');
        //var_dump(ini_get('output_buffering'));
        return $this->render('examples/simple.template.php');
        error_log("__DEBUG__: Chunked: {$response->chunked}, ob_level: " . ob_get_level() . ", ob_length: " . ob_get_length());
        
        //var_dump(ob_get_status(true));
        //return $response;
    }
    
    public function phpinfo($request, $response, $service, $app) {
        if(!$app->conf->debug)
            return $this->error('Access denied!');
        $service->layout('examples/simple.layout.php');
        phpinfo();
    }
}
