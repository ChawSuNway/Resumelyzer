<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;

class GenerateSampleResume extends Command
{
    protected $signature = 'resume:sample {--output=public/samples/sample_resume.docx}';
    protected $description = 'Generate a sample DOCX resume for testing the analyser';

    public function handle(): int
    {
        if (! extension_loaded('zip')) {
            $this->error('The PHP zip extension is required to generate DOCX files.');
            $this->line('Enable it in php.ini: extension=zip');
            $this->line('The plain-text sample is at public/samples/sample_resume.txt');
            return self::FAILURE;
        }

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $phpWord->addFontStyle('name',     ['bold' => true, 'size' => 22, 'color' => '1a1a2e']);
        $phpWord->addFontStyle('contact',  ['size' => 10, 'color' => '555555']);
        $phpWord->addFontStyle('sectionH', ['bold' => true, 'size' => 12, 'color' => '1a56db', 'allCaps' => true]);
        $phpWord->addFontStyle('jobTitle', ['bold' => true, 'size' => 11]);
        $phpWord->addFontStyle('company',  ['italic' => true, 'size' => 10, 'color' => '444444']);
        $phpWord->addFontStyle('body',     ['size' => 10]);
        $phpWord->addFontStyle('bold',     ['bold' => true, 'size' => 10]);

        $phpWord->addParagraphStyle('center', ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $phpWord->addParagraphStyle('tight',  ['spaceAfter' => 60]);
        $phpWord->addParagraphStyle('normal', ['spaceAfter' => 120]);

        $section = $phpWord->addSection([
            'marginTop'    => 720,
            'marginBottom' => 720,
            'marginLeft'   => 1080,
            'marginRight'  => 1080,
        ]);

        // --- Header ---
        $section->addText('Alex Morgan', 'name', 'center');
        $section->addText(
            'alex.morgan@email.com  |  (555) 012-3456  |  linkedin.com/in/alexmorgan  |  San Francisco, CA',
            'contact', 'center'
        );
        $section->addTextBreak(1);

        // --- Section helper ---
        $hr = fn () => $section->addLine(['width' => 9360, 'height' => 0, 'color' => 'cccccc']);

        // --- Summary ---
        $section->addText('Professional Summary', 'sectionH', 'tight');
        $hr();
        $section->addText(
            'Results-driven Full Stack Software Engineer with 6+ years of experience designing scalable web applications. '
            .'Proven track record in startup and enterprise environments. Strong communicator who leads cross-functional teams '
            .'and mentors junior engineers.',
            'body', 'normal'
        );

        // --- Skills ---
        $section->addText('Skills', 'sectionH', 'tight');
        $hr();
        $skills = [
            'Languages'      => 'Python, TypeScript, JavaScript, PHP, SQL, Go',
            'Frameworks'     => 'React, Next.js, Laravel, Node.js, FastAPI, Django',
            'Infrastructure' => 'AWS (EC2, S3, RDS, Lambda), Docker, Kubernetes, Terraform, GitHub Actions',
            'Databases'      => 'PostgreSQL, MySQL, Redis, MongoDB, Elasticsearch',
            'Other'          => 'REST APIs, GraphQL, Agile/Scrum, TDD, CI/CD, System Design',
        ];
        foreach ($skills as $label => $value) {
            $run = $section->addTextRun('tight');
            $run->addText("{$label}: ", 'bold');
            $run->addText($value, 'body');
        }
        $section->addTextBreak(1);

        // --- Experience ---
        $section->addText('Experience', 'sectionH', 'tight');
        $hr();

        $jobs = [
            [
                'title'   => 'Senior Software Engineer',
                'company' => 'TechFlow Inc. — San Francisco, CA',
                'dates'   => 'March 2022 – Present',
                'bullets' => [
                    'Led development of a real-time analytics dashboard for 2M+ DAU; cut page load 62% via Redis caching.',
                    'Shipped multi-tenant Stripe billing module processing $4M+ MRR at 99.98% uptime.',
                    'Mentored 4 junior engineers; 2 promoted within 12 months.',
                    'Migrated monolith to Go + Kubernetes microservices, reducing infrastructure costs by 35%.',
                    'Raised automated test coverage from 22% to 78% (Jest + PHPUnit).',
                ],
            ],
            [
                'title'   => 'Software Engineer',
                'company' => 'Nexus Digital — Remote',
                'dates'   => 'August 2019 – February 2022',
                'bullets' => [
                    'Built RESTful APIs for iOS, Android, and web with < 80 ms median latency.',
                    'Delivered 12 major feature releases across a 2-week sprint cadence.',
                    'Implemented Elasticsearch-powered search, improving relevance score by 41%.',
                    'Reduced cloud spend by $18k/year through EC2 right-sizing and S3 lifecycle policies.',
                ],
            ],
            [
                'title'   => 'Junior Software Engineer',
                'company' => 'BrightSpark Solutions — Austin, TX',
                'dates'   => 'June 2017 – July 2019',
                'bullets' => [
                    'Developed e-commerce features in Laravel/Vue.js for a 50k-user platform.',
                    'Achieved 70% test coverage on all new modules.',
                    'Reduced incident MTTR from 4 hours to 45 minutes via on-call rotation improvements.',
                ],
            ],
        ];

        foreach ($jobs as $job) {
            $run = $section->addTextRun('tight');
            $run->addText($job['title'], 'jobTitle');
            $section->addText($job['company'].'  ·  '.$job['dates'], 'company', 'tight');
            foreach ($job['bullets'] as $bullet) {
                $section->addListItem($bullet, 0, 'body', ['listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED]);
            }
            $section->addTextBreak(1);
        }

        // --- Education ---
        $section->addText('Education', 'sectionH', 'tight');
        $hr();
        $run = $section->addTextRun('tight');
        $run->addText('Bachelor of Science in Computer Science', 'jobTitle');
        $section->addText('University of Texas at Austin  ·  Graduated May 2017  ·  GPA: 3.8 / 4.0', 'company', 'tight');
        $section->addText(
            'Relevant Coursework: Algorithms & Data Structures, Operating Systems, Database Systems, Software Engineering, Computer Networks',
            'body', 'normal'
        );

        // --- Certifications ---
        $section->addText('Certifications', 'sectionH', 'tight');
        $hr();
        $certs = [
            'AWS Certified Solutions Architect – Associate (2023)',
            'Google Cloud Professional Data Engineer (2022)',
            'Certified Scrum Master (CSM) – Scrum Alliance (2021)',
        ];
        foreach ($certs as $cert) {
            $section->addListItem($cert, 0, 'body', ['listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED]);
        }

        // --- Write file ---
        $outputPath = base_path($this->option('output'));
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outputPath);

        $this->info("Sample DOCX written to: {$outputPath}");
        return self::SUCCESS;
    }
}
