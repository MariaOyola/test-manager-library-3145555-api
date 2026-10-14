<?php

namespace Library\Adapters\Inbound\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Library\Application\Services\ProvisionAdminService;

final class ProvisionAdminCommand extends Command
{
    protected $signature = 'library:provision-admin';
    protected $description = 'Provisiona el administrador inicial desde variables de entorno';

    public function handle(ProvisionAdminService $service): int
    {
        $service->execute((string) Str::uuid(), (string) config('library.admin_username'), (string) config('library.admin_password'));
        $this->info('Administrador inicial disponible.');
        return self::SUCCESS;
    }
}
