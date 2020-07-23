<?php

namespace dangthinh104\CommitChecker\Providers;

use dangthinh104\CommitChecker\Commands\InstallHooks;
use dangthinh104\CommitChecker\Commands\InstallPhpCs;
use dangthinh104\CommitChecker\Commands\InstallPhpMD;
use dangthinh104\CommitChecker\Commands\PreCommitHook;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallHooks::class,
                PreCommitHook::class,
                InstallPhpCs::class,
                InstallPhpMD::class,
            ]);
        }
    }
}
