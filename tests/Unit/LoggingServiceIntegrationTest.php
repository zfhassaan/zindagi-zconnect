<?php

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Services\LoggingService;
use Illuminate\Support\Facades\Config;

class LoggingServiceIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Use the 'zindagi' channel which should be registered by the ServiceProvider
        Config::set('zindagi-zconnect.logging.channel', 'zindagi');
    }
    
    public function test_logging_service_writes_to_package_log_file()
    {
        // Calculate expected log path - this should now be in the application's storage/logs/zindagi
        // With daily driver, the file will be suffixed with the date
        $dateSuffix = date('Y-m-d');
        $logPath = storage_path("logs/zindagi/zindagi-{$dateSuffix}.log");

        // Ensure the directory exists (Testbench might not create it automatically)
        if (!file_exists(dirname($logPath))) {
            mkdir(dirname($logPath), 0777, true);
        }
        
        // Clean up before test
        if (file_exists($logPath)) {
            unlink($logPath);
        }

        $loggingService = new LoggingService();
        $loggingService->logInfo('Test integration log message');

        $this->assertFileExists($logPath, "Log file was not created at: " . $logPath);
        $content = file_get_contents($logPath);
        $this->assertStringContainsString('Test integration log message', $content);
    }
}
