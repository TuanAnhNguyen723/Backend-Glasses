<?php

namespace App\Console\Commands;

use App\Services\FakePaymentService;
use Illuminate\Console\Command;

class ExpireFakePaymentSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fake-payments:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đánh dấu các payment session quá hạn thành expired';

    public function handle(FakePaymentService $fakePaymentService): int
    {
        $count = $fakePaymentService->expireOverdueSessions();
        $this->info("Đã cập nhật {$count} phiên thanh toán hết hạn.");

        return self::SUCCESS;
    }
}
