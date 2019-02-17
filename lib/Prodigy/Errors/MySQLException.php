<?php
namespace Prodigy\Errors;

class MySQLException extends \Exception
{
    public function __toString() {
        return __CLASS__ . ": [{$this->code}] {$this->message}\nTRACE:\n" . $this->getTraceAsString() . "\n";
    }
}

?>
