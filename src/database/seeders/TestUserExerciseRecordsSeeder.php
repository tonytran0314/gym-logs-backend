<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\ExerciseRecords;

class TestUserExerciseRecordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Đường dẫn đến thư mục chứa các file JSON
        $directoryPath = base_path('database/data/records');

        // Lấy tất cả các file JSON trong thư mục
        $files = File::files($directoryPath);

        foreach ($files as $file) {
            // Đọc và giải mã dữ liệu JSON trong từng file
            $jsonData = json_decode(File::get($file), true);

            // Kiểm tra nếu có dữ liệu trong file JSON
            if ($jsonData) {
                // Chèn dữ liệu vào bảng ExerciseRecords
                ExerciseRecords::insert($jsonData);
            }
        }
    }
}
