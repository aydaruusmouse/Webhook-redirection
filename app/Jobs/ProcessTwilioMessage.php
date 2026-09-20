<?php

namespace App\Jobs;

use App\Services\TwilioBridge;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessTwilioMessage implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    public function __construct(public array $payload) {}

    public function handle(TwilioBridge $bridge): void
    {
        $bridge->forward($this->payload);
    }
}
