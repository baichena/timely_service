<?php
namespace exception;

use Exception;
use library\Response;
use think\exception\Handle as thinkHandle;
use think\exception\HttpException;
use exception\ResponsableException;


class Handle extends thinkHandle
{
    public function render(Exception $e)
    {
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
        }

        if ($e->getCode() == \exception\ResponsableException::HTTP_METHOD_OPTION) {
            return (new Response)->header((new Response)->cors())->code(204);
        }

        if(preg_match('/^(module|controller|method) not exists/', $e->getMessage())) {
            return (new Response)->api([], ResponsableException::HTTP_NOT_FOUND, 'HTTP NOT FOUND');
        }

        if ($e instanceof \exception\ResponsableException || true ) {
            if($e instanceof \exception\ResponsableException)
                $data = $e->getData();
            else
                $data = [];

            $trace = $e->getTrace();
            $trace_return = [];
            foreach($trace as $trace_item)
            {
                if(isset($trace_item['file']) && preg_match('/think\/App\.php$/', $trace_item['file'])) break;
                $trace_return[] = $trace_item;
            }
            return (new Response)->api($data, $e->getCode(), $e->getMessage(), $e->getFile().'-'.$e->getLine());
        } else {
            return (new Response)->api([], ResponsableException::HTTP_INTERNAL_ERROR, '系统异常');
        }
    }

}
