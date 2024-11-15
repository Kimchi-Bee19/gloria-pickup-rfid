<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentIdentitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $studentIdentities = [
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\xCEEDF9D0\''), // 7-byte binary data
                'notes' => 'Student 10 NFC tag',
                'student_id' => 10,
            ],
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\xB61DD8C0\''), // 7-byte binary data
                'notes' => 'Student 31 NFC tag',
                'student_id' => 31,
            ],
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\x62F6C510\''), // 7-byte binary data
                'notes' => 'Student 12 NFC tag',
                'student_id' => 12,
            ],
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\xC7232240\''), // 7-byte binary data
                'notes' => 'Student 13 NFC tag',
                'student_id' => 13,
            ],
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\x53240F00\''), // 7-byte binary data
                'notes' => 'Student 14 NFC tag',
                'student_id' => 14,
            ],
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\x711BDAB0\''), // 7-byte binary data
                'notes' => 'Student 15 NFC tag',
                'student_id' => 15,
            ],
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\xA7C0A770\''), // 7-byte binary data
                'notes' => 'Student 16 NFC tag',
                'student_id' => 16,
            ],
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\x7942FEC0\''), // 7-byte binary data
                'notes' => 'Student 17 NFC tag',
                'student_id' => 17,
            ],
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\x01F7A460\''), // 7-byte binary data
                'notes' => 'Student 18 NFC tag',
                'student_id' => 18,
            ],
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\x0F0F79E0\''), // 7-byte binary data
                'notes' => 'Student 19 NFC tag',
                'student_id' => 19,
            ],
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\x5F351080\''), // 7-byte binary data
                'notes' => 'Student 20 NFC tag',
                'student_id' => 20,
            ],
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\x6D901AE0\''), // 7-byte binary data
                'notes' => 'Student 21 NFC tag',
                'student_id' => 21,
            ],
            [
                'type' => 'nfc',
                'tag_id' => DB::raw('E\'\\\\x77D756B0\''), // 7-byte binary data
                'notes' => 'Student 22 NFC tag',
                'student_id' => 22,
            ]
        ];

        foreach ($studentIdentities as $identity) {
            DB::table('student_identities')->insert(array_merge($identity, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
