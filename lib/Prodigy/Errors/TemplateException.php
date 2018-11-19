<?php
namespace Prodigy\Errors;

class TemplateException extends \Exception
{
    public function __toString() {
        return __CLASS__ . ": [{$this->code}] {$this->message}\n";
    }
}

?>
