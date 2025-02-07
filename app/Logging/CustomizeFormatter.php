<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\Processor\IntrospectionProcessor;

class CustomizeFormatter
{
    public function __invoke($logger)
    {
        $lineFormatter = new LineFormatter(
            null, // デフォルトのフォーマットを使用
            null,
            true, // 改行を許可
            false // 空のcontextとextraフィールドを含まない
        );

        $introprocessor = new IntrospectionProcessor(Level::Debug, [
            'Monolog\\',
            'Illuminate\\',
        ]);

        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter($lineFormatter);
            $handler->pushProcessor($introprocessor);
        }
    }
}
