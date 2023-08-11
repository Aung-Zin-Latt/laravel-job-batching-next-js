<?php

namespace App\Http\Controllers;

use App\Jobs\SalesCsvProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;

class SalesController extends Controller
{
    public function index() {
        return view('upload-file');
    }

    public function upload() {
        if (request()->has('mycsv')) {
            // $data = array_map('str_getcsv', file(request()->mycsv));
            $data = file(request()->mycsv);

            // If we save 1/2 million of data at the same time, there will be a Maximum execution time of 60 secs exceeded.
            // So. We will make Chunk of 1/2 million data.

            // Chunk files
            $chunks = array_chunk($data, 1000);

            // Convert each 1000 records into a new csv file
            $header = [];

            // Job Batching
            $batch = Bus::batch([])->dispatch();
            // Job Batching
            foreach ($chunks as $key => $chunk) {
                $data = array_map('str_getcsv', $chunk);

                if ($key === 0) {
                    $header = $data[0];
                    unset($data[0]);
                }

                // Failed jobs test
                // Create Error Code, That's mean, if fail, it will go into failed_jobs table
                // if ($key == 2) {
                //     $header = [];
                // }

                $batch->add(new SalesCsvProcess($data, $header));
                // SalesCsvProcess::dispatch($data, $header);
            }

            return $batch;
        }

        return 'Please Upload CSV File';
    }

    // in order to access url, u need to type localhost:8000/batch?id=adjfla_fhdskhf = return value $batch
    public function batch() {
        $batchId = request('id');
        return Bus::findBatch($batchId);
    }

    public function batchInProgress() {
        $batches = DB::table('job_batches')->where('pending_jobs', '>', 0)->get();
        if (count($batches) > 0) {
            // return $batches[0]; // we don't want to return table structure, we want to return with Bus
            return Bus::findBatch($batches[0]->id);
        }
        return [];
    }
}
