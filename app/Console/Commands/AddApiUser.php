<?php

namespace App\Console\Commands;

use App\ApiUser;
use App\Constants\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AddApiUser extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:addapiuser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add API user.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param ApiUser $apiUser
     *
     * @return mixed
     */
    public function handle(ApiUser $apiUser)
    {
        $label = $this->ask('Describe the user by it\'s label');
        $role = $this->choice('Which authorization role is applicable?', [
            Role::MARKETPLACE => Role::MARKETPLACE,
            Role::ADMIN       => Role::ADMIN,
            Role::SUPER_ADMIN => Role::SUPER_ADMIN,
        ]);

        $this->info('label: ' . $label);
        $this->info('role: ' . $role);

        if ( ! $this->confirm('Is this correct?')) {
            $this->info('Abandoned!');

            return null;
        }

        $token = Str::random(80);
        $apiUser->forceFill([
            'api_token' => hash('sha256', $token),
            'label'     => $label,
            'role'      => $role,
        ]);

        $success = $apiUser->save();

        if ($success) {
            $this->info('API user created.');
            $this->info('TOKEN: ' . $token);

            return true;
        }

        $this->error('Couldn\'t create API user.');

        return false;
    }
}
