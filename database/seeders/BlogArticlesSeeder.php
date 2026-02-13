<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlogArticlesSeeder extends Seeder
{
    public function run(): void
    {
        $companyIds = DB::table('owned_companies')->orderBy('id')->pluck('id')->toArray();
        $authorIds = DB::table('employees')->orderBy('id')->pluck('id')->toArray();
        if (empty($companyIds) || empty($authorIds)) {
            return;
        }

        $now = now();
        $articles = $this->articleDefinitions($now);

        foreach ($articles as $def) {
            $companyId = $companyIds[$def['company_index'] % count($companyIds)];
            $authorId = $authorIds[$def['author_index'] % count($authorIds)];

            if (DB::table('articles')->where('owned_company_id', $companyId)->where('slug', $def['slug'])->exists()) {
                continue;
            }

            DB::table('articles')->insert([
                'owned_company_id' => $companyId,
                'author_id' => $authorId,
                'name' => $def['name'],
                'slug' => $def['slug'],
                'keywords' => json_encode($def['keywords']),
                'content' => $def['content'],
                'image' => $def['image'],
                'draft' => $def['draft'],
                'public' => $def['public'],
                'published_at' => $def['published_at'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /** @return array<int, array{company_index: int, author_index: int, name: string, slug: string, keywords: array, content: string, image: ?string, draft: bool, public: bool, published_at: ?string}> */
    private function articleDefinitions($now): array
    {
        return [
            [
                'company_index' => 0,
                'author_index' => 0,
                'name' => '👉 Karōshi (過労死)',
                'slug' => 'karoshi',
                'keywords' => ['karōshi', 'Japan', 'overwork', 'work', 'culture'],
                'content' => '<p>It literally means <strong>"death from overwork"</strong>.</p>
<p><strong>🧠 What Karōshi covers</strong></p>
<p>It has been an official term in Japan since the 1980s. It refers to deaths caused by: heart attacks, stroke, work-related suicide, extreme exhaustion — due to excessive overtime, chronic stress, and a culture of professional sacrifice.</p>
<p><strong>⚖️ Important distinction</strong></p>
<p>It is not a "value" but a social phenomenon. The precise word for work-related death remains <strong>Karōshi</strong>.</p>
<p>この男性は過労死と認定された <em>(This man was certified as having died from overwork)</em></p>',
                'image' => '2-2CeRq5hqaeaQ.jfif',
                'draft' => false,
                'public' => true,
                'published_at' => $now,
            ],
            [
                'company_index' => 0,
                'author_index' => 1,
                'name' => 'Kaizen (改善) — Continuous improvement',
                'slug' => 'kaizen',
                'keywords' => ['kaizen', 'Japan', 'continuous improvement', 'Toyota', 'management', 'quality'],
                'content' => '<p><strong>Kaizen</strong> (改善) literally means "change for the better" or <strong>continuous improvement</strong>.</p>
<p>A central concept of Japanese management, popularised by Toyota. Key principles: small steps, Gemba (go and see), no blame, standardise then improve — PDCA.</p>
<p>The ukiyo-e print in the header contrasts with Kaizen’s low-key, steady progress.</p>',
                'image' => '3-Wm8RuKfF5oc1.jpg',
                'draft' => true,
                'public' => true,
                'published_at' => null,
            ],
            [
                'company_index' => 1,
                'author_index' => 0,
                'name' => 'Nemawashi (根回し)',
                'slug' => 'nemawashi',
                'keywords' => ['nemawashi', 'Japan', 'consensus', 'meetings', 'decision-making'],
                'content' => '<p>Literally <strong>"laying the roots"</strong> — preparing the ground so a plant (or decision) can take hold.</p>
<p><strong>🧠 What Nemawashi is</strong></p>
<p>Informally sounding out people and building consensus <em>before</em> a formal meeting or decision. Ideas are shared one-on-one so that by the time the group meets, everyone is aligned.</p>
<p><strong>Why it’s used</strong></p>
<ul><li>Avoids public surprise or loss of face</li><li>Leads to smoother, faster decisions in the room</li><li>Respects hierarchy and relationships</li></ul>
<p>Common in Japanese business and politics. The formal meeting then confirms what was already agreed in private.</p>',
                'image' => null,
                'draft' => false,
                'public' => true,
                'published_at' => $now,
            ],
            [
                'company_index' => 1,
                'author_index' => 1,
                'name' => 'Honne & Tatemae (本音と建前)',
                'slug' => 'honne-tatemae',
                'keywords' => ['honne', 'tatemae', 'Japan', 'communication', 'culture'],
                'content' => '<p><strong>Honne</strong> (本音) is one’s true feelings; <strong>Tatemae</strong> (建前) is the "front" or face one shows in public to keep harmony.</p>
<p><strong>🧠 How they work together</strong></p>
<p>In Japanese communication both matter: tatemae preserves relationships and social order; honne is shared only in trusted settings. Reading the room means sensing when someone is speaking honne vs tatemae.</p>
<p><strong>In practice</strong></p>
<ul><li>Official stance (tatemae) vs private opinion (honne)</li><li>Refusing indirectly to avoid saying no (tatemae)</li><li>Understanding both is key to doing business in Japan</li></ul>',
                'image' => null,
                'draft' => false,
                'public' => false,
                'published_at' => $now,
            ],
            [
                'company_index' => 0,
                'author_index' => 1,
                'name' => 'Shokunin (職人) — The craftsman spirit',
                'slug' => 'shokunin',
                'keywords' => ['shokunin', 'Japan', 'craftsmanship', 'quality', 'mastery'],
                'content' => '<p>A <strong>shokunin</strong> is a craftsperson who pursues mastery and takes pride in work well done — not just for pay but for the quality of the result.</p>
<p><strong>🧠 Shokunin kishitsu (職人気質)</strong></p>
<p>This "craftsman spirit" includes:</p>
<ul><li>Dedication to the craft</li><li>Attention to detail</li><li>Continuous improvement</li><li>Pride in the outcome, not just the output</li></ul>
<p>Applied beyond traditional crafts to modern work: doing the job right for its own sake.</p>',
                'image' => null,
                'draft' => true,
                'public' => true,
                'published_at' => null,
            ],
            [
                'company_index' => 2,
                'author_index' => 0,
                'name' => 'Mottainai (もったいない)',
                'slug' => 'mottainai',
                'keywords' => ['mottainai', 'Japan', 'waste', 'sustainability', 'respect'],
                'content' => '<p><strong>Mottainai</strong> expresses regret when something useful is wasted — "what a waste" with a sense that the object or resource deserved better use.</p>
<p><strong>🧠 What it covers</strong></p>
<p>It underpins a mindset of:</p>
<ul><li>Reducing waste</li><li>Reusing and repairing</li><li>Respecting resources and the effort that went into them</li></ul>
<p>Often used in sustainability and environmental campaigns. Nobel laureate Wangari Maathai promoted "mottainai" as a global concept for reducing waste.</p>',
                'image' => null,
                'draft' => false,
                'public' => true,
                'published_at' => $now,
            ],
            [
                'company_index' => 2,
                'author_index' => 1,
                'name' => 'Senpai / Kōhai (先輩・後輩)',
                'slug' => 'senpai-kohai',
                'keywords' => ['senpai', 'kohai', 'Japan', 'hierarchy', 'mentorship'],
                'content' => '<p><strong>Senpai</strong> (先輩) is someone senior in experience or position; <strong>kōhai</strong> (後輩) is someone junior.</p>
<p><strong>🧠 The relationship</strong></p>
<p>Respect and guidance: senpai teaches and looks out for kōhai; kōhai learns and supports. The bond is vertical but often warm and lasting.</p>
<p><strong>Where you see it</strong></p>
<ul><li>School: older vs younger students</li><li>Work: experienced vs new colleagues</li><li>Arts and sports: senior vs junior members</li></ul>
<p>Expectations go both ways: senpai has responsibility; kōhai shows respect and effort.</p>',
                'image' => null,
                'draft' => false,
                'public' => false,
                'published_at' => $now,
            ],
            [
                'company_index' => 0,
                'author_index' => 0,
                'name' => 'Gaman (我慢)',
                'slug' => 'gaman',
                'keywords' => ['gaman', 'Japan', 'perseverance', 'endurance', 'culture'],
                'content' => '<p><strong>Gaman</strong> means enduring hardship without complaining — persevering with patience and self-control.</p>
<p><strong>🧠 Role in culture</strong></p>
<p>Valued in Japanese culture in work and daily life. It can foster resilience and dignity under pressure.</p>
<p><strong>⚖️ The flip side</strong></p>
<p>When expectations are too high, gaman can be linked to excessive stress or suppressed feelings. Like Karōshi, it reflects social pressure as much as personal choice.</p>
<p>Balance matters: endure what’s meaningful, but not at the cost of health.</p>',
                'image' => null,
                'draft' => true,
                'public' => false,
                'published_at' => null,
            ],
            [
                'company_index' => 1,
                'author_index' => 0,
                'name' => 'Hansei (反省) — Reflection',
                'slug' => 'hansei',
                'keywords' => ['hansei', 'Japan', 'reflection', 'improvement', 'self-criticism'],
                'content' => '<p><strong>Hansei</strong> is sincere reflection on one’s mistakes or shortcomings in order to improve. It is not blame but a step toward growth.</p>
<p><strong>🧠 How it’s used</strong></p>
<p>In education and business, after a failure or project, time is set aside to:</p>
<ul><li>Reflect honestly on what went wrong</li><li>Acknowledge one’s own part</li><li>Plan concrete changes</li></ul>
<p>Often paired with <strong>kaizen</strong>: reflect (hansei), then improve (kaizen). No improvement without honest reflection first.</p>',
                'image' => null,
                'draft' => false,
                'public' => true,
                'published_at' => $now,
            ],
            [
                'company_index' => 1,
                'author_index' => 1,
                'name' => 'Pomodoro Technique',
                'slug' => 'pomodoro',
                'keywords' => ['pomodoro', 'productivity', 'focus', 'time management'],
                'content' => '<p>The <strong>Pomodoro Technique</strong> uses timed work blocks (e.g. 25 minutes) followed by short breaks to keep focus and reduce burnout.</p>
<p><strong>🧠 How it works</strong></p>
<ul><li>Choose a task</li><li>Work for one "pomodoro" (e.g. 25 min) without interruption</li><li>Short break (5 min), then repeat</li><li>After 4 pomodoros, take a longer break (15–30 min)</li></ul>
<p><strong>Why it helps</strong></p>
<p>Creates rhythm, makes time visible, and protects deep work from distraction. Widely used in study and professional settings.</p>',
                'image' => null,
                'draft' => true,
                'public' => true,
                'published_at' => null,
            ],
            [
                'company_index' => 2,
                'author_index' => 0,
                'name' => 'Wabi-sabi (侘寂)',
                'slug' => 'wabi-sabi',
                'keywords' => ['wabi-sabi', 'Japan', 'aesthetics', 'imperfection', 'simplicity'],
                'content' => '<p><strong>Wabi-sabi</strong> finds beauty in imperfection, impermanence, and simplicity. Nothing is complete or permanent; wear and age add meaning.</p>
<p><strong>🧠 Two ideas</strong></p>
<ul><li><strong>Wabi</strong> — simplicity, humility, the beauty of the modest</li><li><strong>Sabi</strong> — the passage of time, the beauty of the weathered and imperfect</li></ul>
<p><strong>Where you see it</strong></p>
<p>Tea ceremony, pottery, gardens, design: valuing the modest, the incomplete, and the transient rather than the flawless and new.</p>',
                'image' => null,
                'draft' => false,
                'public' => true,
                'published_at' => $now,
            ],
            [
                'company_index' => 2,
                'author_index' => 1,
                'name' => 'Ikigai (生き甲斐)',
                'slug' => 'ikigai',
                'keywords' => ['ikigai', 'Japan', 'purpose', 'meaning', 'life'],
                'content' => '<p><strong>Ikigai</strong> is often translated as "reason for being" — what makes life worth living.</p>
<p><strong>🧠 The four overlaps</strong></p>
<p>It is sometimes shown as the overlap of:</p>
<ul><li>What you love</li><li>What you’re good at</li><li>What the world needs</li><li>What you can be paid for (or what sustains you)</li></ul>
<p><strong>In practice</strong></p>
<p>Finding ikigai is associated with longevity and satisfaction. It can be work, family, hobby, or community — not one universal formula.</p>',
                'image' => null,
                'draft' => false,
                'public' => true,
                'published_at' => $now,
            ],
        ];
    }
}
