<?php
require_once __DIR__ . '/db.php';

function content_defaults(): array {
    return [
        'hero' => [
            'eyebrow' => 'Available for new projects',
            'h1_1'    => 'The',
            'h1_2'    => 'operating system',
            'h1_3'    => 'for',
            'h1_4'    => 'modern brands.',
            'sub'     => "Strategy, design and engineering that lets ambitious teams ship like a Series\u{2011}D engineering org\u{00A0}— without becoming one.",
            'cta1'    => 'Start a project',
            'cta2'    => 'View work',
        ],
        'marquee' => ['Farzat Amin', 'Sakibun Noor', 'Shafin'],
        'services' => [
            ['num'=>'// 01','title'=>'Brand systems &<br>visual identity','desc'=>'Identities engineered to scale across every surface — from a 16×16 favicon to a 60-foot conference banner. We deliver tokens, not PDFs.','img'=>'assets/services/svc-01.jpg','tags'=>['Identity','Wordmarks','Type pairing','Motion','Tokens']],
            ['num'=>'// 02','title'=>'Product design &<br>UX systems','desc'=>'Design systems, complex flows, and the unglamorous parts: empty states, errors, density modes.','img'=>'assets/services/svc-02.jpg','tags'=>['Figma','DS','Prototype']],
            ['num'=>'// 03','title'=>'Engineering<br>& performance','desc'=>"Next.js, Laravel, Phoenix. Sub-second TTFB on AA+ accessibility — or we don't ship.",'img'=>'assets/services/svc-03.jpg','tags'=>['Next.js','PHP','Edge']],
            ['num'=>'// 04','title'=>'Growth &<br>conversion engineering','desc'=>'Funnel diagnostics, landing-page systems, ICP research, and the experimentation infra to run 18 tests a quarter without breaking the brand.','img'=>'assets/services/svc-04.jpg','tags'=>['CRO','SEO','Lifecycle','Attribution']],
            ['num'=>'// 05','title'=>'Content & narrative','desc'=>"Editorial calendars, launch narratives, sales enablement that doesn't read like sales enablement.",'img'=>'assets/services/svc-05.jpg','tags'=>[]],
            ['num'=>'// 06','title'=>'Strategy & research','desc'=>'Positioning, ICP discovery, competitive teardowns. The 30-page memo before the design begins.','img'=>'assets/services/svc-06.jpg','tags'=>[]],
            ['num'=>'// 07','title'=>'Ongoing partnership','desc'=>"Quarterly retainers. Roadmap. Sprint reviews. We become the marketing & design org you don't have.",'img'=>'assets/services/svc-07.jpg','tags'=>[]],
        ],
        'team' => [
            ['name'=>'Farzat Amin','role'=>'Founder · Strategy & Brand','glyph'=>'FA'],
            ['name'=>'Sakibun Noor','role'=>'Engineering & Performance','glyph'=>'SN'],
            ['name'=>'Shafin','role'=>'Design & Product Systems','glyph'=>'SH'],
        ],
        'faq' => [
            ['q'=>'What kind of teams hire Digital Harbor?','a'=>"Funded startups (Series A–C), public-co innovation orgs, and the occasional category-defining solo founder. The common thread: a brand that's outgrowing the deck it was launched on, and a team that wants velocity without rebuilding their internal org."],
            ['q'=>'How is this different from a traditional agency?','a'=>"Three things. First, we don't do handoffs — strategy, design, engineering, and growth all sit on one Linear board. Second, we ship in two-week sprints, not in months-long phases. Third, our retainer model means you book the senior operators, not the junior staff who get assigned after the SOW closes."],
            ['q'=>'What does a typical engagement look like?','a'=>"A two-week diagnostic, then a 90-day quarterly retainer with a 5–7 person pod (strategy, design, engineering, growth). Most clients renew for 3–4 quarters. We've had partnerships run as long as four years."],
            ['q'=>'Can you work alongside our internal team?','a'=>"Always. About 60% of our work is augmenting in-house teams — we plug into your Slack, your Linear, your weekly review. We sign your IP and security agreements as a vendor, not a contractor."],
            ['q'=>"What's a project budget look like?",'a'=>"Quarterly retainers start at $90K and scale to $400K depending on pod size. One-off launches (a new site, a brand refresh, a launch film) range $40K–$180K. We're transparent about hourly rates if you'd rather buy hours than outcomes."],
            ['q'=>'How fast can you start?','a'=>"Two to four weeks from signed SOW. We turn down more work than we take to keep that number honest."],
        ],
    ];
}

function content_ensure_table(): void {
    db()->exec("CREATE TABLE IF NOT EXISTS content (
        k          VARCHAR(120) NOT NULL PRIMARY KEY,
        v          LONGTEXT NOT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by INT UNSIGNED NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function content_load(): array {
    try {
        $stmt = db()->prepare('SELECT v FROM content WHERE k = ?');
        $stmt->execute(['site']);
        $row = $stmt->fetch();
    } catch (PDOException $e) {
        // Table missing — auto-create then retry
        if (str_contains($e->getMessage(), 'content')) {
            content_ensure_table();
            $stmt = db()->prepare('SELECT v FROM content WHERE k = ?');
            $stmt->execute(['site']);
            $row = $stmt->fetch();
        } else throw $e;
    }
    if (!$row) return content_defaults();
    $data = json_decode($row['v'], true);
    return is_array($data) ? array_replace_recursive(content_defaults(), $data) : content_defaults();
}

function content_save(array $data, ?int $userId = null): void {
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $sql = 'INSERT INTO content (k,v,updated_by) VALUES (?,?,?)
            ON DUPLICATE KEY UPDATE v = VALUES(v), updated_by = VALUES(updated_by)';
    db()->prepare($sql)->execute(['site', $json, $userId]);
}
