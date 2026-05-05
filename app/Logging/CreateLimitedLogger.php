<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\Logger;

/**
 * Factory untuk channel log kustom dengan batas jumlah entri.
 * Didaftarkan di config/logging.php via driver "custom".
 */
class CreateLimitedLogger
{
    public function __invoke(array $config): Logger
    {
        $level      = Level::fromName($config['level'] ?? 'debug');
        $maxEntries = (int) ($config['max_entries'] ?? 50);
        $path       = $config['path'] ?? storage_path('logs/laravel.log');

        $handler = new LimitedStreamHandler($maxEntries, $path, $level);

        // Format ringkas: tanggal | level | pesan | konteks (tanpa stack trace verbose)
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s',
            true,  // allowInlineLineBreaks
            true,  // ignoreEmptyContextAndExtra
        );
        $handler->setFormatter($formatter);

        return new Logger('laravel', [$handler]);
    }
}
