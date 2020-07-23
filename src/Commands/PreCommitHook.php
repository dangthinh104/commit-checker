<?php

namespace dangthinh104\CommitChecker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;

class PreCommitHook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:pre-commit-hook {--psr2|psr2} {--phpmd|phpmd} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hook before commit GIT';

    /**
     * Execute the console command.
     *
     *
     *
     * @return mixed
     */
    public function handle()
    {
        if (!file_exists(base_path(".git"))) {
            $this->output->error('Please install git first | git init');
            return false;
        }
        if (!file_exists(base_path(".git/hooks/pre-commit"))) {
            $this->warn('Please install pre-commit hooks |php artisan git:install-hooks');
            return false;
        }

        $changed = $this->getChangedPhpFiles();
        if (empty($changed)) {
            $this->info('Success: Nothing to check!');
            return false;
        }
        $start = now();
        switch (true) {
            case $this->option('phpmd'):
                $statusCheck = $this->checkPMD($changed);
                break;
            default:
                $statusCheck = $this->checkPsr2($changed);
                break;
        }
        // Inform checked files and time
        $this->output->writeln('Checked '.count($changed).' file(s) in '.now()->diffInRealSeconds($start).' second(s)');
        // If error handle git
        if (isset($statusCheck) && !$statusCheck) {
            exit($this->fails());
        }
        $this->info('Your code is perfect, no syntax error found!');
        return 0;
    }

    /**
     * Get a list of changed PHP files
     *
     * @return array
     */
    protected function getChangedPhpFiles(): array
    {
        $changed = [];

        foreach ($this->getChangedFiles() as $path) {
            if (Str::endsWith($path, '.php') && !Str::endsWith($path, '.blade.php')) {
                $changed[] = $path;
            }
        }

        return $changed;
    }

    /**
     * Get a list of changed files
     *
     * @return array
     */
    protected function getChangedFiles(): array
    {

        if (!$this->exec($cmd = 'git status --short', $output)) {
            throw new RuntimeException('Unable to run command: '.$cmd);
        }

        $changed = [];

        foreach ($output as $line) {
            if ($path = $this->parseGitStatus($line)) {
                $changed[] = $path;
            }
        }

        return $changed;
    }

    /**
     * Execute the command, return true if status is success, false otherwise
     *
     * @param  string  $command
     * @param  array &$output
     * @param  int &$status
     *
     * @return bool
     */
    protected function exec(string $command, &$output = null, &$status = null): bool
    {
        exec($command, $output, $status);

        return $status == 0;
    }

    /**
     * Parses the git status line and return the changed file or null if the
     * file hasn't changed.
     *
     * @param  string  $line
     *
     * @return string|null
     */
    protected function parseGitStatus(string $line): ?string
    {
        if (!preg_match('/^(.)(.)\s(\S+)(\s->\S+)?$/', $line, $matches)) {
            return null; // ignore incorrect lines
        }

        list(, $first, $second, $path) = $matches;

        if (!in_array($first, ['M', 'A'])) {
            return null;
        }

        return $path;
    }

    /**
     * Command failed message, returns 1
     *
     * @return int
     */
    protected function fails()
    {
        $message = 'Commit aborted: you have errors in your code!';

        if ($this->exec('which cowsay')) {
            $this->exec('cowsay -f unipony-smaller "{$message}"', $output);
            $message = implode("\n", $output);
        }

        $this->output->writeln('<fg=red>'.$message.'</fg=red>');

        return 1;
    }

    /**
     * Checks the PSR-2 compliance of changed files
     *
     * @param  array  $changed
     *
     * @return boolean
     */
    protected function checkPsr2(array $changed): bool
    {
        if (!file_exists(base_path('phpcs.xml'))) {
            $this->output->error('Please import file rule phpcs.xml | php artisan git:create-phpcs');
            return false;
        }

        $ignored = [
            '*/database/*',
            '*/public/*',
            '*/assets/*',
            '*/vendor/*',
        ];

        $options = [
            '--standard='.base_path('phpcs.xml'),
            '--ignore='.implode(',', $ignored),
        ];

        if (!$this->option('no-ansi')) {
            $options[] = '--colors';
        }
        $this->info('Start checking PSR-2 Coding Standard...');
        $cmd = base_path('vendor/bin/phpcs').' '.implode(' ', $options).' '.implode(' ', $changed);

        $status = $this->exec($cmd, $output);

        if (!$this->option('quiet') && $output) {
            $this->output->writeln(implode("\n", $output));
        }
        return $status;
    }

    /**
     * Check PHP Mess Detector
     *
     * @param  array  $changedAllFiles
     *
     * @return bool
     * @author quoc_thinh
     */
    protected function checkPMD(array $changedAllFiles): bool
    {
        if (!file_exists(base_path('phpmd.xml'))) {
            $this->output->error('Please import file rule phpmd.xml | php artisan git:create-phpmd');
            return false;
        }
        $options = [
            'ansi',
            base_path('phpmd.xml'),
            '--ignore-violations-on-exit'

        ];
        $this->info('Start running PHP Mess Detector...');
        $statusMaster = 0;
        foreach ($changedAllFiles as $file) {
            $cmd = base_path('vendor/bin/phpmd').' '.$file.' '.implode(' ', $options);
            $status = $this->exec($cmd, $output);
            if (!$status && $output) {
                $this->output->writeln(implode("\n", $output));
                // reset output file if have error
                $output = null;
                $statusMaster = $status;
            }
        }
        $dgsd = 'fd';
        return $statusMaster;
    }
}
