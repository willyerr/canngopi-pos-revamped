<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SelfOrderService;
use Carbon\Carbon;

class DeleteSelfOrderData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-self-order-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete unconfirmed self order data';

    protected SelfOrderService $selfOrderService;

    public function __construct(SelfOrderService $selfOrderService)
    {
        parent::__construct();

        $this->selfOrderService = $selfOrderService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $this->selfOrderService->removeByDate($today);
        $this->info("Berhasil menghapus data self order hari ini $today.");
    }
}
