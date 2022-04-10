<?php
namespace App\Helpers\Logging;

class DailyFormatter{
    /**
     * 自定义给定的日志实例。
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->getFormatter()->allowInlineLineBreaks(false);
        }
    }
}
