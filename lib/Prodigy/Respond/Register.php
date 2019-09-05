<?php
namespace Prodigy\Respond;

class Register extends Respond
{
    // Just show an agreement (rules) text.
    public function agreement($request, $response, $service, $app)
    {
        $db_prefix = $app->db->prefix;
        $dbst = $app->db->query("SELECT value FROM {$db_prefix}settings WHERE variable='agreement'");
        $service->agreement = $dbst->fetchColumn();
        $dbst = null;
        
        return $this->render('register/agreement.template.php');
    }
}
