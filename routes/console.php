<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about:manager-library', function (): void {
    $this->info('Manager Library API');
});
