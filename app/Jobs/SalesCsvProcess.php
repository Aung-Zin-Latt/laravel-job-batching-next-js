<?php

namespace App\Jobs;

use App\Models\Sales;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;

class SalesCsvProcess implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    public $header;

    public function __construct($data, $header)
    {
        $this->data = $data;
        $this->header = $header;
        // dd("Data that we want to insert", $this->data, "Database Column Names", $this->header);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->data as $sale) {
            $saleData = array_combine($this->header, $sale);
            Sales::create($saleData);
        }
    }

    public function failed(Throwable $exception): void
    {
        // Send user notification of failure, etc...
    }
}
