<?php

declare(strict_types=1);

return [
    'name' => 'Automotive Risk Management Partners, Inc. (ARMP)',

    'short_name' => 'ARMP',

    'website' => 'https://autorisknow.com',

    'phone' => '815-345-3629',

    'tagline' => 'An all-in-one dealership risk management program designed to help auto and RV dealerships reduce operational and compliance risk.',

    'positioning' => 'One Partner, One Solution for the FTC Safeguards Rule and broader dealership risk — replacing separate vendors for cybersecurity, OSHA/EPA, finance/GLBA audits, and physical risk.',

    'audience' => 'Automotive and RV dealership principals, GMs, compliance officers, and F&I leadership across the US (Maine to Hawaii).',

    'history' => 'Founded more than 20 years ago by Terry Dortch, who created the first GLBA auditing process for dealership sales and finance centers. The team brings 40+ years of combined automotive industry experience.',

    'mission' => 'Provide a proven risk assessment plus continuous monitoring of physical and electronic risks, with a focus on simplicity, performance, and security.',

    'offering' => 'A single platform combining compliance audits, real-time compliance monitoring, employee training, and cybersecurity (Ridgeback Network Defense) — replacing a stack of separate vendors.',

    'value_props' => [
        'One Partner, One Solution: cyber, OSHA/EPA, GLBA finance audit, and physical risk under one vendor.',
        'Built specifically for dealerships, not a generic GRC tool.',
        '20+ years of dealership compliance experience; team holds 40+ years of automotive industry background.',
        'Continuous monitoring of physical and electronic risk, not just point-in-time audits.',
        'Ongoing audit reviews typically run 4–6 hours after the initial install.',
        'Nationwide coverage — Maine to Hawaii.',
    ],

    'regulatory_coverage' => [
        'FTC / GLBA Safeguards Rule (including Continuous Monitoring requirements)',
        'OFAC screening',
        'OSHA workplace safety (29 CFR 1910)',
        'EPA environmental compliance',
        'CFPB practices',
        'FTC CARS Rule',
        'TILA / Regulation Z',
        'DOT training',
        'EEOC / ADA employment compliance',
    ],

    'products' => [
        'ridgeback' => [
            'name' => 'Ridgeback Network Defense',
            'description' => 'Purpose-built network appliance for continuous, real-time monitoring of dealership IT, OT, and IoT — including DMS, POS, diagnostic systems, office infrastructure, and building controls (HVAC, lighting). Uses an "Offense-for-Defense" approach with phantom endpoints to disrupt attackers during reconnaissance and prevent lateral movement, rather than reacting after a breach.',
            'capabilities' => [
                '24/7/365 vulnerability scanning',
                'Real-time detection of open ports, unpatched software, and rogue devices',
                'Layer-2 visibility including devices that cannot run an agent',
                'Lateral-movement prevention and immediate intruder eviction',
                'Automated executive summaries and technical remediation plans',
                'Satisfies FTC Safeguards Rule "Continuous Monitoring" requirements',
            ],
        ],
    ],

    'packages' => [
        [
            'name' => 'Compliance Core',
            'fit' => 'Dealerships needing foundational compliance in one solution.',
            'includes' => [
                'GLBA / Safeguards program coverage',
                'OSHA program coverage (29 CFR 1910)',
                'Staff training modules',
                'Required documentation and manuals',
                'Continuous program updates',
            ],
        ],
        [
            'name' => 'Compliance Core + Scans',
            'fit' => 'Dealerships that want compliance plus measurable testing verification.',
            'includes' => [
                'Everything in Compliance Core',
                'Scheduled vulnerability scanning',
                'Detailed findings and reporting',
                'Remediation tracking portal',
                'Cyber-risk measurable metrics',
            ],
        ],
        [
            'name' => 'Ridgeback Defense',
            'fit' => 'Dealerships needing continuous network visibility rather than periodic assessments.',
            'includes' => [
                'Ridgeback Network Defense platform',
                'Continuous 24/7 monitoring',
                'Real-time threat disruption',
                'Asset discovery and tracking',
            ],
        ],
        [
            'name' => 'Total Defense Suite',
            'fit' => 'Larger dealerships and groups wanting comprehensive, defensible protection.',
            'includes' => [
                'Complete Compliance Core (GLBA + OSHA)',
                'Ridgeback Network Defense',
                'Consolidated group reporting',
                'Priority incident response access',
                'True real-time viewing with no latency',
                'Phantom endpoint deployment',
                '24/7 monitoring',
                'Custom policy creation',
            ],
        ],
    ],

    'email_guidelines' => [
        'Lead with a dealership-specific risk or compliance angle, not generic CRM/sales pitches.',
        'Reference relevant regulations by name when it fits (Safeguards Rule, CARS Rule, OFAC, OSHA, etc.).',
        'When recommending a package, match it to the dealership\'s size and apparent maturity (Compliance Core for smaller single-rooftops, Total Defense Suite for groups).',
        'Use product names exactly as listed (Ridgeback Network Defense, Compliance Core, Total Defense Suite).',
        'Keep emails short: a hook tied to their world, one or two specifics, a low-friction CTA (15-minute call or quick demo).',
        'Avoid hype, fear-mongering, or absolute guarantees ("100% compliant", "eliminate all risk"). Do not invent pricing, features, certifications, or partners not listed in this config.',
    ],
];
