<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\OwnedCompany;
use App\Models\TeamMember;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        if (Event::query()->exists()) {
            return;
        }

        $ownedCompanies = OwnedCompany::query()->get();
        if ($ownedCompanies->isEmpty()) {
            return;
        }

        $teamMemberIds = TeamMember::query()->pluck('id')->toArray();
        if (empty($teamMemberIds)) {
            return;
        }

        $faker = \Faker\Factory::create();
        $colors = array_keys(Event::COLORS);

        $eventTitles = [
            'Team Meeting',
            'Project Review',
            'Client Call',
            'Sprint Planning',
            'Design Review',
            'Code Review Session',
            'Product Demo',
            'Training Session',
            'Quarterly Review',
            'Brainstorming Session',
            'Budget Meeting',
            'Performance Review',
            'Strategy Session',
            'Lunch & Learn',
            'Workshop',
        ];

        foreach ($ownedCompanies as $ownedCompany) {
            // Create 15-25 events per company
            $eventCount = $faker->numberBetween(15, 25);

            for ($i = 0; $i < $eventCount; $i++) {
                $creatorId = $faker->randomElement($teamMemberIds);
                $startDate = Carbon::now()
                    ->addDays($faker->numberBetween(-14, 30))
                    ->setTime($faker->numberBetween(8, 17), $faker->randomElement([0, 15, 30, 45]));

                $allDay = $faker->boolean(20); // 20% chance of all-day event
                $duration = $faker->randomElement([30, 60, 90, 120, 180]); // minutes

                $event = Event::create([
                    'owned_company_id' => $ownedCompany->id,
                    'title' => $faker->randomElement($eventTitles),
                    'description' => $faker->optional(0.5)->sentence(),
                    'type' => Event::TYPE_USER,
                    'start_at' => $allDay ? $startDate->startOfDay() : $startDate,
                    'end_at' => $allDay ? $startDate->copy()->endOfDay() : $startDate->copy()->addMinutes($duration),
                    'all_day' => $allDay,
                    'color' => $faker->randomElement($colors),
                    'created_by' => $creatorId,
                ]);

                // Add 0-4 participants (excluding creator)
                $otherMembers = array_diff($teamMemberIds, [$creatorId]);
                if (!empty($otherMembers)) {
                    $participantCount = $faker->numberBetween(0, min(4, count($otherMembers)));
                    if ($participantCount > 0) {
                        $participants = $faker->randomElements($otherMembers, $participantCount);
                        foreach ($participants as $participantId) {
                            $event->participants()->attach($participantId, [
                                'status' => $faker->randomElement([
                                    Event::STATUS_INVITED,
                                    Event::STATUS_ACCEPTED,
                                    Event::STATUS_ACCEPTED,
                                    Event::STATUS_DECLINED,
                                ]),
                            ]);
                        }
                    }
                }
            }
        }
    }
}
