<?php

namespace App\Listeners;

use App\Models\API\V1\Tokens;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Session;

class DeleteToken
{
    private $user;
    public function __construct()
    {
        $this->user = Session::get("user_id");
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        Tokens::destroy($this->user);
    }
}
