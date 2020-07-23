<?php

namespace dangthinh104\CommitChecker\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class InstallPhpMD extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:create-phpmd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default phpmd.xml';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $phpMD = __DIR__ . '/../../phpmd.xml';
        $rootPhpMD= base_path('phpmd.xml');

        // Checkout existence of sample phpcs.xml.
        if (!file_exists($phpMD)) {
            $this->error('The sample phpmd.xml does not exist! Try to reinstall dangthinh104/commit-checker package!');

            return 1;
        }

        // Checkout existence phpcs.xml in root path of project.
        if (file_exists($rootPhpMD)) {
            if (!$this->confirmToProceed('phpmd.xml already exists, do you want to overwrite it?', true)) {
                return 1;
            }

            // Remove old phpcs.xml file form root
            unlink($rootPhpMD);
        }

        $this->writePHPCS($phpMD, $rootPhpMD)
            ? $this->info('phpmd.xml successfully created!')
            : $this->error('Unable to create phpmd.xml');

        return 0;
    }

    /**
     * Copy phpmd.xml file to root and return true on success, false otherwise.
     *
     * @param string $phpMD
     * @param string $rootPhpMD
     * @return bool
     */
    protected function writePHPCS(string $phpMD, string $rootPhpMD): bool
    {
        // phpcs.xml file to root
        if (!copy($phpMD, $rootPhpMD)) {
            return false;
        }

        return true;
    }
}
