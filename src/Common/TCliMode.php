<?php

namespace QscmfApiCommon;

use Think\Log;

trait TCliMode
{
    protected string $log_file_name;

    protected function setLogFileName($log_file_name):void{
        if (!IS_CLI)  die('The file can only be run in cli mode!');
        $this->log_file_name = $log_file_name;
    }

    public function writeErrorLog($message, $need_stdout = false):void{
        if (empty($this->log_file_name)) E("log_file_name is empty");

        $need_stdout && fwrite(STDOUT, $message);
        Log::write($message, '', '', $this->getLogFileFullName());
    }

    public function getLogFileFullName():string{
        return LARA_DIR. DIRECTORY_SEPARATOR. 'storage/logs/'.$this->log_file_name;
    }

}