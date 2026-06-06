<?php

use App\Enums\DigestCadence;
use Illuminate\Support\Facades\Schedule;

DigestCadence::current()->schedule(Schedule::command('digest:send'));
