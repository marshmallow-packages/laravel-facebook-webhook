<?php

namespace Marshmallow\LaravelFacebookWebhook\Commands;

use Illuminate\Console\Command;
use Marshmallow\LaravelFacebookWebhook\Controllers\FacebookTokenController;

class LaravelFacebookWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    public $signature = 'marshmallow:setup-facebook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup webhook for Facebook Lead Webhook';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tokenController    = new FacebookTokenController();

        $this->info('Authenticate using the link below:');
        $this->info($tokenController->getRedirectUrl());

        if (!$this->confirm('Are you authenticated on Facebook?')) {
            $this->info('Goodbye!');
            return 0;
        }

        $tokenController = new FacebookTokenController();
        if (!$tokenController->installApp()) {
            $this->warning('App installation failed!');
            return 0;
        } else {
            $this->info('App installation successfull..');
        }

        if (!$tokenController->installWebhook()) {
            $this->warning('Webhook installation failed!');
            return 0;
        } else {
            $this->info('Webhook installation successfull..');
        }

        $this->info('All done');

        return 0;
    }
}
