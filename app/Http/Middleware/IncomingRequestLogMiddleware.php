<?php

namespace App\Http\Middleware;

use Closure;
use App\Lib\Logs\IncomingRequest as requestLog;

class IncomingRequestLogMiddleware
{
    /**
     * log an incoming request params.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected static $importantGets = [
        'v1/getdeliveryinfo'
    ];

    public function handle($request, Closure $next) {
        $pathInfo = $request->getRequestUri();
        $method = $request->getMethod();
        if ('GET' == $method && !$this->isUrlImportant($pathInfo)) {
            return $next($request);
        }

        $inputStr = '';
        $params = $request->input();
        foreach ($params as $k => $v) {
            $inputStr .= $k ."=". $v . '&';
        }
        $inputStr = trim($inputStr, '&');

        $requestLogContent = '[' . $method . '] ' .$pathInfo .'?' .$inputStr;

        $logger = new requestLog();
        $logger->info($requestLogContent);
        return $next($request);
    }

    private function isUrlImportant($url) {
        foreach (self::$importantGets as $uri) {
            if (false !== strpos($uri, $url)) {
                return true;
            }
        }
        return false;
    }
}
