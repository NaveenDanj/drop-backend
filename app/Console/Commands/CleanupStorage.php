<?php

namespace App\Console\Commands;

use App\Models\UserFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drop:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup the file storage and delete expired files';

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
     * @return int
     */
    public function handle()
    {
        echo "Cleaning up storage...\n";
        // get all the files
        $user_files = UserFile::all();

        // loop through the files
        foreach ($user_files as $user_file) {

            // get today timestamp in miliseconds
            $today_time = time() * 1000;

            // get the file created datetinm and convert it into miliseconds
            $file_created_time = strtotime($user_file->created_at) * 1000;
            // get the expired timestamp and add it
            $expired_time = $file_created_time + $user_file->expired_time;

            // if the file is expired
            if ($expired_time < $today_time) {
                // delete the file
                if(Storage::disk('local')->exists("files/{$user_file->name}")){
                    Storage::disk('local')->delete("files/{$user_file->name}");
                }
            }

        }

    }
}
