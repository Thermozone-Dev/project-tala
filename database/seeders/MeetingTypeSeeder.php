<?php

namespace Database\Seeders;

use App\Models\MeetingType;
use Illuminate\Database\Seeder;

class MeetingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $meetingTypes = [
            [
                'name' => 'Google Meet',
                'url' => 'https://meet.google.com/new',
            ],
            [
                'name' => 'Microsoft Teams',
                'url' => 'https://teams.live.com/v2/',
            ],
            [
                'name' => 'Zoom',
                'url' => 'https://zoom.us/start/videomeeting',
            ],
        ];

        foreach ($meetingTypes as $type) {
            MeetingType::updateOrCreate(
                ['name' => $type['name']],
                ['url' => $type['url']]
            );
        }
    }
}
