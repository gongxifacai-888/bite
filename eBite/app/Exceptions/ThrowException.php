<?php


namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Throwable;

class ThrowException extends Exception
{

    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setLine($line)
    {
        $this->line = $line;
    }

    /**
     * @param Request   $request
     * @param Exception $exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request)
    {
        $msg = $this->getMessage();
        $file = $this->getFile();
        $line = $this->getLine();

        if (config('app.debug')) {
            $file=str_replace(base_path(),'',$file);
            $msg = "{$msg}";
            // $msg = "{$msg},文件:{$file},{$line}行";
        }

        if ($request->isXmlHttpRequest()) {
            return json_response_error($msg);
        }
        abort($this->code, $msg);
    }

    public function report()
    {
        $msg = $this->getMessage();
        $file = $this->getFile();
        $line = $this->getLine();
        if (config('app.debug')) {
            $msg = "{$msg}";
            // $msg = "{$msg},文件:{$file},{$line}行";
            Log::error($msg);
        }
    }

}
