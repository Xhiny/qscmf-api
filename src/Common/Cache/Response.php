<?php
namespace QscmfApiCommon\Cache;

class Response{

    public $message;
    public $status;
    public $data;
    public $code;
    public $extra_res_data;

    public function __construct($message, $status, $data = '', $code = 200, array $extra_res_data =[]){
        $this->message = $message;
        $this->status = $status;
        $this->data = $data;
        $this->code = $code;
        $this->extra_res_data = $extra_res_data;
    }

    public function toArray():array{
        return [
            'message' => $this->message,
            'status' => $this->status,
            'data' => $this->data,
            'code' => $this->code,
            'extra_res_data' => $this->extra_res_data,
        ];
    }

    public function toJson():string{
        return json_encode($this->toArray());
    }
}