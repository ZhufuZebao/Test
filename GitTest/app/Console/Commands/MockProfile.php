<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MockProfile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mock:user_profile';

    /**di
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mock up User Profile for every users';

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
     * @return mixed
     */
    public function handle()
    {
        $faker = \Faker\Factory::create('ja_JP');
        $users = \App\User::all();

        foreach($users as $u)
        {
            // INSERT INTO user_profiles VALUES (...);

            if(!$u->profile)
                $u->profile()->create([
                    'title'   => $faker->word,
                    'content' => $faker->text,
                ]);
        }
    }
}
