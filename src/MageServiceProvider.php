<?php declare(strict_types=1);

namespace Mage;

use Illuminate\Support\ServiceProvider;
use Mage\Framework\FrameworkServiceProvider;

/** @psalm-api */
final class MageServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->register(FrameworkServiceProvider::class);
    }
}
