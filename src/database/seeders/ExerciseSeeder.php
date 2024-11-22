<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Exercise;
use Illuminate\Support\Facades\File;

class ExerciseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to the exercises directory
        $exercisesDir = database_path('data/exercises');
        
        // Check if the directory exists
        if (File::isDirectory($exercisesDir)) {
            // Get all JSON files in the directory
            $files = File::files($exercisesDir);
            
            foreach ($files as $file) {
                // Read and decode the JSON file
                $jsonData = json_decode(File::get($file), true);

                // Insert data into the database
                Exercise::insert($jsonData);
            }
        } else {
            $this->command->error("The directory {$exercisesDir} does not exist.");
        }
    }
}
