<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class StoreSetting {

    /**
     * In-memory cache for loaded settings to optimize multiple reads.
     */
    private static ?array $cachedSettings = null;

    /**
     * Catalog of all system store settings, grouped with default values and labels.
     */
    public static function getCatalog(): array {
        return [
            // -----------------------------------------------------------------
            // 1. BRAND & IDENTITY
            // -----------------------------------------------------------------
            'brand' => [
                'title'       => 'Brand Identity & Legal Profile',
                'description' => 'Configure your luxury brand name, legal entity coordinates, tax identification numbers, and store logos.',
                'icon'        => 'crown',
                'settings'    => [
                    'store_name' => [
                        'label'       => 'Store Name',
                        'default'     => 'Jiyaji Collection - Luxury Ethnic Wear',
                        'type'        => 'text',
                        'placeholder' => 'e.g. Jiyaji Collection',
                        'help'        => 'Publicly displayed in title tags, invoices, emails, and header.',
                    ],
                    'store_tagline' => [
                        'label'       => 'Brand Tagline / Slogan',
                        'default'     => 'Timeless Indian Heritage & Bespoke Bridal Couture',
                        'type'        => 'text',
                        'placeholder' => 'e.g. Timeless Indian Heritage & Bespoke Bridal Couture',
                        'help'        => 'Displayed under store logo and in brand marketing banners.',
                    ],
                    'store_description' => [
                        'label'       => 'Brand Bio & Meta Description',
                        'default'     => 'Handcrafted royal sherwanis, bespoke lehengas, silk kurtas, and heritage bridal attire designed with timeless Indian craftsmanship and royal splendor.',
                        'type'        => 'textarea',
                        'placeholder' => 'Describe your atelier and collection...',
                        'help'        => 'Used as the default SEO fallback meta description across public pages.',
                    ],
                    'store_legal_name' => [
                        'label'       => 'Registered Legal Entity Name',
                        'default'     => 'Jiyaji Luxury Apparels Private Limited',
                        'type'        => 'text',
                        'placeholder' => 'e.g. Jiyaji Luxury Apparels Pvt. Ltd.',
                        'help'        => 'Printed on formal GST invoices, tax returns, and legal compliance disclaimers.',
                    ],
                    'store_gstin' => [
                        'label'       => 'GSTIN (Goods & Services Tax ID)',
                        'default'     => '08AAAAA0000A1Z5',
                        'type'        => 'text',
                        'placeholder' => '15-digit Indian GSTIN, e.g. 08AAAAA0000A1Z5',
                        'help'        => 'Mandatory 15-character GST identification number for Indian B2C & B2B invoices.',
                    ],
                    'store_pan' => [
                        'label'       => 'Permanent Account Number (PAN)',
                        'default'     => 'AAACJ1234K',
                        'type'        => 'text',
                        'placeholder' => '10-character PAN',
                        'help'        => 'Corporate PAN for banking, payment gateway KYC, and tax audits.',
                    ],
                    'store_cin' => [
                        'label'       => 'Corporate Identification Number (CIN)',
                        'default'     => 'U17100RJ2026PTC089123',
                        'type'        => 'text',
                        'placeholder' => '21-digit Corporate CIN',
                        'help'        => 'Ministry of Corporate Affairs company registration number.',
                    ],
                    'store_logo_url' => [
                        'label'       => 'Primary Store Logo (Light Background)',
                        'default'     => '',
                        'type'        => 'asset',
                        'placeholder' => 'Path or URL to primary SVG/PNG logo',
                        'help'        => 'Recommended dimensions: 320x80px with transparent background.',
                    ],
                    'store_logo_dark_url' => [
                        'label'       => 'Dark Mode / Inverted Store Logo',
                        'default'     => '',
                        'type'        => 'asset',
                        'placeholder' => 'Path or URL to dark mode logo',
                        'help'        => 'Used on dark navigation headers and deep royal backdrops.',
                    ],
                    'store_favicon_url' => [
                        'label'       => 'Browser Favicon (.ico, .png, .svg)',
                        'default'     => '',
                        'type'        => 'asset',
                        'placeholder' => 'Path or URL to 32x32 favicon',
                        'help'        => 'Displayed in browser tabs and bookmarks bar.',
                    ],
                ]
            ],

            // -----------------------------------------------------------------
            // 2. CONTACT & CONCIERGE
            // -----------------------------------------------------------------
            'contact' => [
                'title'       => 'Contact Channels & Concierge HQ',
                'description' => 'Configure official customer support mailboxes, escalation phone numbers, and physical atelier headquarters.',
                'icon'        => 'phone-call',
                'settings'    => [
                    'contact_email' => [
                        'label'       => 'Primary Concierge Email',
                        'default'     => 'concierge@jiyajicollection.com',
                        'type'        => 'email',
                        'placeholder' => 'concierge@yourstore.com',
                        'help'        => 'Primary point of contact for customer queries, bespoke sizing, and styling advice.',
                    ],
                    'contact_orders_email' => [
                        'label'       => 'Orders & Fulfillment Desk Email',
                        'default'     => 'orders@jiyajicollection.com',
                        'type'        => 'email',
                        'placeholder' => 'orders@yourstore.com',
                        'help'        => 'Automated order confirmation sender and logistics correspondence desk.',
                    ],
                    'contact_phone' => [
                        'label'       => 'Concierge Telephone / Mobile',
                        'default'     => '+91 98765 43210',
                        'type'        => 'text',
                        'placeholder' => '+91 98765 43210',
                        'help'        => 'Direct telephone line displayed in footer, invoices, and helpdesk portal.',
                    ],
                    'contact_toll_free' => [
                        'label'       => 'Toll-Free Helpline',
                        'default'     => '1800 123 4567',
                        'type'        => 'text',
                        'placeholder' => '1800 123 4567',
                        'help'        => 'Pan-India toll-free support line for bridal and high-value orders.',
                    ],
                    'contact_address_line1' => [
                        'label'       => 'Atelier / Headquarters Line 1',
                        'default'     => 'Jiyaji Palace, Plot 14, Heritage Boulevard',
                        'type'        => 'text',
                        'placeholder' => 'Building, Suite, Street name',
                        'help'        => 'Official business operating address.',
                    ],
                    'contact_address_line2' => [
                        'label'       => 'Address Line 2 / Landmark',
                        'default'     => 'Near City Palace, M.I. Road',
                        'type'        => 'text',
                        'placeholder' => 'Area or Landmark',
                        'help'        => 'Secondary landmark or commercial zone.',
                    ],
                    'contact_city' => [
                        'label'       => 'City',
                        'default'     => 'Jaipur',
                        'type'        => 'text',
                        'placeholder' => 'City',
                        'help'        => 'Headquarters city.',
                    ],
                    'contact_state' => [
                        'label'       => 'State / Province',
                        'default'     => 'Rajasthan',
                        'type'        => 'text',
                        'placeholder' => 'State',
                        'help'        => 'Headquarters state.',
                    ],
                    'contact_pincode' => [
                        'label'       => 'Postal / PIN Code',
                        'default'     => '302001',
                        'type'        => 'text',
                        'placeholder' => '6-digit PIN code',
                        'help'        => 'Headquarters dispatch PIN code.',
                    ],
                    'contact_country' => [
                        'label'       => 'Country',
                        'default'     => 'India',
                        'type'        => 'text',
                        'placeholder' => 'Country',
                        'help'        => 'Primary country of jurisdiction.',
                    ],
                    'contact_hours' => [
                        'label'       => 'Operating / Concierge Hours',
                        'default'     => 'Mon – Sat: 10:00 AM – 8:00 PM IST',
                        'type'        => 'text',
                        'placeholder' => 'Mon – Sat: 10:00 AM – 8:00 PM IST',
                        'help'        => 'Hours when VIP styling concierge and phone operators are available.',
                    ],
                ]
            ],

            // -----------------------------------------------------------------
            // 3. WHATSAPP FLOATING CONCIERGE (Feature 30)
            // -----------------------------------------------------------------
            'whatsapp' => [
                'title'       => 'WhatsApp Floating Concierge Widget',
                'description' => 'Enable instant WhatsApp customer interactions with customizable floating button, default preset inquiry text, and welcome callout bubble.',
                'icon'        => 'message-circle',
                'settings'    => [
                    'whatsapp_enabled' => [
                        'label'       => 'Enable WhatsApp Floating Widget',
                        'default'     => '1',
                        'type'        => 'boolean',
                        'help'        => 'Toggle the floating WhatsApp button across all public storefront pages.',
                    ],
                    'whatsapp_number' => [
                        'label'       => 'WhatsApp Business Phone Number',
                        'default'     => '+919876543210',
                        'type'        => 'text',
                        'placeholder' => 'Include country code, e.g. +919876543210',
                        'help'        => 'Visitors clicking the button will initiate a WhatsApp chat with this number.',
                    ],
                    'whatsapp_default_message' => [
                        'label'       => 'Default Pre-filled Message',
                        'default'     => "Namaste Jiyaji Concierge! I'm interested in bespoke luxury couture and would love personalized styling assistance.",
                        'type'        => 'textarea',
                        'placeholder' => 'Pre-filled text when visitor opens chat...',
                        'help'        => 'URL-encoded message automatically placed in the visitor\'s WhatsApp chat window.',
                    ],
                    'whatsapp_position' => [
                        'label'       => 'Floating Button Position',
                        'default'     => 'bottom-right',
                        'type'        => 'select',
                        'options'     => [
                            'bottom-right' => 'Bottom Right (Standard)',
                            'bottom-left'  => 'Bottom Left',
                        ],
                        'help'        => 'Screen placement of the floating widget on desktop and mobile browsers.',
                    ],
                    'whatsapp_button_title' => [
                        'label'       => 'Floating Button Hover Label',
                        'default'     => 'Chat with Bridal Stylist',
                        'type'        => 'text',
                        'placeholder' => 'e.g. Chat with Stylist',
                        'help'        => 'Tooltip and accessibility label shown beside the WhatsApp icon.',
                    ],
                    'whatsapp_popup_enabled' => [
                        'label'       => 'Show Welcome Greeting Callout Bubble',
                        'default'     => '1',
                        'type'        => 'boolean',
                        'help'        => 'Displays a stylish floating callout speech bubble beside the button to invite conversations.',
                    ],
                    'whatsapp_popup_heading' => [
                        'label'       => 'Greeting Bubble Heading',
                        'default'     => 'Need Styling Advice?',
                        'type'        => 'text',
                        'placeholder' => 'e.g. Need Styling Advice?',
                        'help'        => 'Catchy heading on the greeting bubble.',
                    ],
                    'whatsapp_popup_text' => [
                        'label'       => 'Greeting Bubble Subtext',
                        'default'     => 'Connect directly with our royal wedding couture specialist on WhatsApp for custom sizing, swatches, and styling guidance.',
                        'type'        => 'textarea',
                        'placeholder' => 'Short invitation message...',
                        'help'        => 'Engaging invitation text displayed inside the callout bubble.',
                    ],
                    'whatsapp_delay_seconds' => [
                        'label'       => 'Greeting Bubble Appearance Delay (seconds)',
                        'default'     => '3',
                        'type'        => 'number',
                        'placeholder' => '3',
                        'help'        => 'Seconds to wait after page load before smoothly animating the callout bubble into view.',
                    ],
                ]
            ],

            // -----------------------------------------------------------------
            // 4. LOCALIZATION & CURRENCY
            // -----------------------------------------------------------------
            'localization' => [
                'title'       => 'Localization, Currency & Regional Formats',
                'description' => 'Configure base currency, symbols, decimal formatting, timezone, and calendar date displays.',
                'icon'        => 'globe',
                'settings'    => [
                    'currency_code' => [
                        'label'       => 'Base ISO Currency Code',
                        'default'     => 'INR',
                        'type'        => 'text',
                        'placeholder' => 'e.g. INR',
                        'help'        => 'Standard 3-letter currency code used for payment processing.',
                    ],
                    'currency_symbol' => [
                        'label'       => 'Currency Symbol',
                        'default'     => '₹',
                        'type'        => 'text',
                        'placeholder' => 'e.g. ₹',
                        'help'        => 'Display glyph printed next to prices (e.g. ₹).',
                    ],
                    'currency_position' => [
                        'label'       => 'Currency Symbol Placement',
                        'default'     => 'before',
                        'type'        => 'select',
                        'options'     => [
                            'before' => 'Before amount (e.g. ₹24,999)',
                            'after'  => 'After amount (e.g. 24,999 ₹)',
                        ],
                        'help'        => 'Whether symbol appears immediately before or after the numeric amount.',
                    ],
                    'currency_decimals' => [
                        'label'       => 'Decimal Precision',
                        'default'     => '0',
                        'type'        => 'select',
                        'options'     => [
                            '0' => 'No Decimals (e.g. ₹24,999 — Standard Luxury Fashion)',
                            '2' => '2 Decimals (e.g. ₹24,999.00)',
                        ],
                        'help'        => 'Number of fractional decimal places to display.',
                    ],
                    'timezone' => [
                        'label'       => 'Default System Timezone',
                        'default'     => 'Asia/Kolkata',
                        'type'        => 'select',
                        'options'     => [
                            'Asia/Kolkata'     => 'Asia/Kolkata (IST - UTC+05:30)',
                            'UTC'              => 'UTC (Coordinated Universal Time)',
                            'Asia/Dubai'       => 'Asia/Dubai (GST - UTC+04:00)',
                            'Europe/London'    => 'Europe/London (GMT/BST - UTC+00:00)',
                            'America/New_York' => 'America/New_York (EST - UTC-05:00)',
                        ],
                        'help'        => 'Timezone used to calculate orders, coupon expiries, and sales trends.',
                    ],
                    'date_format' => [
                        'label'       => 'Admin & Public Date Format',
                        'default'     => 'd M Y',
                        'type'        => 'select',
                        'options'     => [
                            'd M Y'  => '21 Sep 2026 (d M Y)',
                            'd/m/Y'  => '21/09/2026 (d/m/Y)',
                            'Y-m-d'  => '2026-09-21 (ISO Y-m-d)',
                            'M j, Y' => 'Sep 21, 2026 (M j, Y)',
                        ],
                        'help'        => 'Format applied when rendering calendar timestamps.',
                    ],
                    'time_format' => [
                        'label'       => 'Time Clock Format',
                        'default'     => '12h',
                        'type'        => 'select',
                        'options'     => [
                            '12h' => '12-hour Clock with AM/PM (e.g. 04:30 PM)',
                            '24h' => '24-hour Military Clock (e.g. 16:30)',
                        ],
                        'help'        => 'Standard for displaying audit logs and order timestamps.',
                    ],
                ]
            ],

            // -----------------------------------------------------------------
            // 5. MARKETING & TRACKING PIXELS (Meta Pixel, GA4, GTM)
            // -----------------------------------------------------------------
            'marketing' => [
                'title'       => 'Tracking Pixels, Analytics & Custom Scripts',
                'description' => 'Inject Meta (Facebook) Pixel for ad retargeting, Google Analytics 4 (GA4), and custom conversion tracking tags.',
                'icon'        => 'activity',
                'settings'    => [
                    'meta_pixel_enabled' => [
                        'label'       => 'Enable Meta (Facebook) Pixel',
                        'default'     => '1',
                        'type'        => 'boolean',
                        'help'        => 'Activates Meta Pixel script injection for Catalog View, AddToCart, and Purchase events.',
                    ],
                    'meta_pixel_id' => [
                        'label'       => 'Meta Pixel ID',
                        'default'     => '987654321098765',
                        'type'        => 'text',
                        'placeholder' => '15-16 digit Pixel ID, e.g. 987654321098765',
                        'help'        => 'Found in your Meta Business Suite & Events Manager dashboard.',
                    ],
                    'ga4_enabled' => [
                        'label'       => 'Enable Google Analytics 4 (GA4)',
                        'default'     => '1',
                        'type'        => 'boolean',
                        'help'        => 'Injects gtag.js measurement library across the storefront.',
                    ],
                    'ga4_measurement_id' => [
                        'label'       => 'GA4 Measurement ID',
                        'default'     => 'G-JYJ9988776',
                        'type'        => 'text',
                        'placeholder' => 'e.g. G-XXXXXXXXXX',
                        'help'        => 'Google Analytics 4 Data Stream Measurement ID.',
                    ],
                    'gtm_container_id' => [
                        'label'       => 'Google Tag Manager Container ID',
                        'default'     => 'GTM-JYJLX01',
                        'type'        => 'text',
                        'placeholder' => 'e.g. GTM-XXXXXXX',
                        'help'        => 'Optional Google Tag Manager container ID.',
                    ],
                    'header_custom_scripts' => [
                        'label'       => 'Custom Header Scripts (Inside <head>)',
                        'default'     => '',
                        'type'        => 'code',
                        'placeholder' => '<script>/* Custom verification or tracking tag */</script>',
                        'help'        => 'Injected directly before the closing </head> tag on all public pages (e.g. Pinterest tag, domain verification).',
                    ],
                    'footer_custom_scripts' => [
                        'label'       => 'Custom Body Scripts (Before </body>)',
                        'default'     => '',
                        'type'        => 'code',
                        'placeholder' => '<script>/* Custom chatbot or marketing script */</script>',
                        'help'        => 'Injected directly before the closing </body> tag on public pages.',
                    ],
                ]
            ],

            // -----------------------------------------------------------------
            // 6. SOCIAL MEDIA & CHANNELS
            // -----------------------------------------------------------------
            'social' => [
                'title'       => 'Social Media Profiles & Brand Community',
                'description' => 'Link your official Instagram, Facebook, YouTube, Pinterest, and Twitter/X channels to power social icons across your storefront.',
                'icon'        => 'share-2',
                'settings'    => [
                    'social_instagram' => [
                        'label'       => 'Instagram Profile URL',
                        'default'     => 'https://instagram.com/jiyajicollection',
                        'type'        => 'url',
                        'placeholder' => 'https://instagram.com/yourbrand',
                        'help'        => 'Primary channel for haute couture runways, bridal shoots, and client testimonials.',
                    ],
                    'social_facebook' => [
                        'label'       => 'Facebook Page URL',
                        'default'     => 'https://facebook.com/jiyajicollection',
                        'type'        => 'url',
                        'placeholder' => 'https://facebook.com/yourbrand',
                        'help'        => 'Official Facebook brand fan page.',
                    ],
                    'social_youtube' => [
                        'label'       => 'YouTube Channel URL',
                        'default'     => 'https://youtube.com/@jiyajicollection',
                        'type'        => 'url',
                        'placeholder' => 'https://youtube.com/@yourbrand',
                        'help'        => 'Behind-the-scenes artisan videos, fashion week shows, and fabric stories.',
                    ],
                    'social_pinterest' => [
                        'label'       => 'Pinterest Profile URL',
                        'default'     => 'https://pinterest.com/jiyajicollection',
                        'type'        => 'url',
                        'placeholder' => 'https://pinterest.com/yourbrand',
                        'help'        => 'Bridal lehenga moodboards, color swatches, and wedding inspiration pins.',
                    ],
                    'social_twitter' => [
                        'label'       => 'X / Twitter Profile URL',
                        'default'     => 'https://twitter.com/jiyajicollection',
                        'type'        => 'url',
                        'placeholder' => 'https://twitter.com/yourbrand',
                        'help'        => 'Corporate updates, press announcements, and media releases.',
                    ],
                ]
            ],

            // -----------------------------------------------------------------
            // 7. STORE OPERATIONS & MAINTENANCE
            // -----------------------------------------------------------------
            'operations' => [
                'title'       => 'Store Operations & Maintenance Control',
                'description' => 'Emergency maintenance toggles, order intake switches, global low-stock inventory limits, and administrative order alerts.',
                'icon'        => 'shield-alert',
                'settings'    => [
                    'maintenance_mode' => [
                        'label'       => 'Maintenance Mode (Atelier Refresh)',
                        'default'     => '0',
                        'type'        => 'boolean',
                        'help'        => 'When activated, non-admin visitors see a luxury maintenance notice while admin panel remains accessible.',
                    ],
                    'maintenance_message' => [
                        'label'       => 'Public Maintenance Notice',
                        'default'     => 'Our atelier is currently being refreshed with royal wedding season collections. We will return online shortly. For urgent bespoke inquiries, please reach out via our WhatsApp concierge.',
                        'type'        => 'textarea',
                        'placeholder' => 'Notice shown to storefront visitors during maintenance...',
                        'help'        => 'Displayed prominently on the maintenance screen.',
                    ],
                    'order_acceptance' => [
                        'label'       => 'Accepting Online Orders',
                        'default'     => '1',
                        'type'        => 'boolean',
                        'help'        => 'When paused, checkout is disabled with a polite message requesting customers to bookmark items or reach out via concierge.',
                    ],
                    'low_stock_threshold' => [
                        'label'       => 'Global Low Stock Warning Threshold',
                        'default'     => '5',
                        'type'        => 'number',
                        'placeholder' => '5',
                        'help'        => 'Variants with inventory at or below this count trigger critical stock alerts.',
                    ],
                    'allow_guest_checkout' => [
                        'label'       => 'Allow Guest Checkout (Without Mandatory Account)',
                        'default'     => '1',
                        'type'        => 'boolean',
                        'help'        => 'Enables frictionless checkout for first-time luxury shoppers.',
                    ],
                    'min_order_value' => [
                        'label'       => 'Minimum Order Checkout Value (₹)',
                        'default'     => '0',
                        'type'        => 'number',
                        'placeholder' => '0 for no minimum',
                        'help'        => 'Orders below this amount cannot proceed to payment. Set to 0 to disable.',
                    ],
                    'admin_order_notification_email' => [
                        'label'       => 'Admin Order Alert Notification Email',
                        'default'     => 'alerts@jiyajicollection.com',
                        'type'        => 'email',
                        'placeholder' => 'alerts@yourbrand.com',
                        'help'        => 'Receives real-time email dispatch notifications on newly placed customer orders.',
                    ],
                ]
            ],
        ];
    }

    // =========================================================================
    // 1. ENSURE DEFAULTS (DATABASE SEEDER)
    // =========================================================================

    /**
     * Ensure all store setting keys and labels exist in the site_settings table.
     * Uses non-destructive INSERT ... ON DUPLICATE KEY UPDATE so existing customized
     * values are never overwritten.
     */
    public static function ensureDefaults(): void {
        try {
            $db = Database::connect();
            $catalog = self::getCatalog();

            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, label, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    label = COALESCE(label, VALUES(label))
            ");

            foreach ($catalog as $groupKey => $group) {
                foreach ($group['settings'] as $key => $meta) {
                    $defVal = (string)$meta['default'];
                    $label  = (string)$meta['label'];
                    $stmt->bind_param("sss", $key, $defVal, $label);
                    $stmt->execute();
                }
            }
            $stmt->close();

            // Clear in-memory cache
            self::$cachedSettings = null;
        } catch (Exception $e) {
            error_log("StoreSetting::ensureDefaults error: " . $e->getMessage());
        }
    }

    // =========================================================================
    // 2. READ METHODS
    // =========================================================================

    /**
     * Retrieve all settings loaded into an associative array with metadata.
     */
    public static function getAllSettings(bool $refresh = false): array {
        if (self::$cachedSettings !== null && !$refresh) {
            return self::$cachedSettings;
        }

        self::ensureDefaults();

        $rawSettings = [];
        try {
            $db = Database::connect();
            $res = $db->query("
                SELECT s.setting_key, s.setting_value, s.label, s.updated_by, s.updated_at, a.name AS updated_by_name
                FROM site_settings s
                LEFT JOIN admins a ON s.updated_by = a.id
            ");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $rawSettings[$row['setting_key']] = $row;
                }
            }
        } catch (Exception $e) {
            error_log("StoreSetting::getAllSettings error: " . $e->getMessage());
        }

        $catalog = self::getCatalog();
        $structured = [];

        foreach ($catalog as $groupKey => $group) {
            $structured[$groupKey] = [
                'title'       => $group['title'],
                'description' => $group['description'],
                'icon'        => $group['icon'],
                'settings'    => [],
            ];

            foreach ($group['settings'] as $key => $meta) {
                $dbRow = $rawSettings[$key] ?? null;
                $val = ($dbRow !== null && $dbRow['setting_value'] !== null)
                    ? $dbRow['setting_value']
                    : (string)$meta['default'];

                $structured[$groupKey]['settings'][$key] = array_merge($meta, [
                    'key'             => $key,
                    'value'           => $val,
                    'updated_by_name' => $dbRow['updated_by_name'] ?? null,
                    'updated_at'      => $dbRow['updated_at'] ?? null,
                ]);
            }
        }

        self::$cachedSettings = $structured;
        return $structured;
    }

    /**
     * Retrieve a specific group of settings.
     */
    public static function getGroup(string $group): array {
        $all = self::getAllSettings();
        return $all[$group] ?? [];
    }

    /**
     * Get a single setting value directly by key.
     */
    public static function get(string $key, $default = null): ?string {
        $all = self::getAllSettings();
        foreach ($all as $group) {
            if (isset($group['settings'][$key])) {
                return $group['settings'][$key]['value'] ?? (string)$default;
            }
        }

        // Fallback query if setting not in catalog (e.g. shipping/payment settings)
        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1");
            $stmt->bind_param("s", $key);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                return $row['setting_value'];
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("StoreSetting::get fallback error: " . $e->getMessage());
        }

        return $default !== null ? (string)$default : null;
    }

    /**
     * Convenience helper to return key-value dictionary for quick lookups.
     */
    public static function getFlatValues(): array {
        $all = self::getAllSettings();
        $flat = [];
        foreach ($all as $group) {
            foreach ($group['settings'] as $key => $item) {
                $flat[$key] = $item['value'];
            }
        }
        return $flat;
    }

    // =========================================================================
    // 3. WRITE & UPDATE METHODS
    // =========================================================================

    /**
     * Batch update store settings with optional admin ID for audit tracking.
     *
     * @param array $data Key-value pairs to update
     * @param int|null $adminId ID of admin user performing the action
     * @return bool True if successful
     */
    public static function updateSettings(array $data, ?int $adminId = null): bool {
        try {
            $db = Database::connect();
            $catalog = self::getCatalog();

            // Compile map of valid keys and their labels
            $validKeys = [];
            foreach ($catalog as $group) {
                foreach ($group['settings'] as $key => $meta) {
                    $validKeys[$key] = $meta['label'];
                }
            }

            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, label, updated_by, updated_at)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    setting_value = VALUES(setting_value),
                    label         = VALUES(label),
                    updated_by    = VALUES(updated_by),
                    updated_at    = NOW()
            ");

            foreach ($data as $k => $v) {
                if (!isset($validKeys[$k])) {
                    continue; // Skip keys not in store settings catalog
                }

                $label = $validKeys[$k];
                $valStr = is_bool($v) ? ($v ? '1' : '0') : (string)$v;

                $stmt->bind_param("sssi", $k, $valStr, $label, $adminId);
                $stmt->execute();
            }

            $stmt->close();

            // Clear in-memory cache
            self::$cachedSettings = null;
            return true;
        } catch (Exception $e) {
            error_log("StoreSetting::updateSettings error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fast toggle for Boolean settings (WhatsApp, Maintenance, Order Acceptance, etc.).
     */
    public static function toggleSetting(string $key, ?int $adminId = null): bool {
        $currentVal = self::get($key, '0');
        $newVal = ($currentVal === '1' || $currentVal === 'true') ? '0' : '1';

        return self::updateSettings([$key => $newVal], $adminId);
    }

    // =========================================================================
    // 4. ASSET UPLOAD HANDLER (LOGO, FAVICON)
    // =========================================================================

    /**
     * Upload store logo or favicon securely to public/assets/images/store/.
     *
     * @param array $file $_FILES element
     * @param string $type 'logo', 'logo_dark', or 'favicon'
     * @param int|null $adminId
     * @return string|null Web-accessible relative URL on success, null on error
     */
    public static function uploadAsset(array $file, string $type, ?int $adminId = null): ?string {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $targetDir = dirname(__DIR__, 2) . '/public/assets/images/store';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'svg', 'ico'];
        $allowedMimes = [
            'image/png',
            'image/jpeg',
            'image/webp',
            'image/svg+xml',
            'image/x-icon',
            'image/vnd.microsoft.icon'
        ];

        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions, true)) {
            return null;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMimes, true) && $ext !== 'ico') {
            return null;
        }

        // Limit size to 2.5MB
        if ($file['size'] > 2.5 * 1024 * 1024) {
            return null;
        }

        $newFileName = 'store_' . $type . '_' . time() . '.' . $ext;
        $destPath = $targetDir . '/' . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $webPath = 'public/assets/images/store/' . $newFileName;

            // Map type to setting key
            $keyMap = [
                'logo'      => 'store_logo_url',
                'logo_dark' => 'store_logo_dark_url',
                'favicon'   => 'store_favicon_url',
            ];

            if (isset($keyMap[$type])) {
                self::updateSettings([$keyMap[$type] => $webPath], $adminId);
            }

            return $webPath;
        }

        return null;
    }

    // =========================================================================
    // 5. EXECUTIVE KPIS & HEALTH STATUS
    // =========================================================================

    /**
     * Compute store configuration KPIs for executive overview banner.
     */
    public static function getKPIs(): array {
        $flat = self::getFlatValues();

        // 1. Profile Completeness Score
        $coreBrandKeys = [
            'store_name', 'store_tagline', 'store_description',
            'store_legal_name', 'store_gstin', 'store_pan',
            'contact_email', 'contact_phone', 'contact_address_line1',
            'contact_city', 'contact_pincode'
        ];
        $filledCount = 0;
        foreach ($coreBrandKeys as $k) {
            if (!empty($flat[$k])) {
                $filledCount++;
            }
        }
        $profileScore = round(($filledCount / count($coreBrandKeys)) * 100);

        // 2. WhatsApp Concierge State
        $whatsappEnabled = !empty($flat['whatsapp_enabled']);
        $whatsappNumber  = $flat['whatsapp_number'] ?? '';

        // 3. Marketing & Tracking Pixels
        $metaPixelActive = !empty($flat['meta_pixel_enabled']) && !empty($flat['meta_pixel_id']);
        $ga4Active       = !empty($flat['ga4_enabled']) && !empty($flat['ga4_measurement_id']);
        $gtmActive       = !empty($flat['gtm_container_id']);
        $activePixelsCount = ($metaPixelActive ? 1 : 0) + ($ga4Active ? 1 : 0) + ($gtmActive ? 1 : 0);

        // 4. Store Operations
        $isMaintenance   = !empty($flat['maintenance_mode']);
        $acceptingOrders = !empty($flat['order_acceptance']);

        // 5. Most recent update timestamp
        $latestUpdate = 'Just now';
        try {
            $db = Database::connect();
            $res = $db->query("SELECT MAX(updated_at) AS last_update FROM site_settings");
            if ($res && $r = $res->fetch_assoc()) {
                if (!empty($r['last_update'])) {
                    $latestUpdate = date('d M Y, h:i A', strtotime($r['last_update']));
                }
            }
        } catch (Exception $e) {
            // ignore
        }

        return [
            'profile_completeness' => $profileScore,
            'whatsapp_enabled'     => $whatsappEnabled,
            'whatsapp_number'      => $whatsappNumber,
            'meta_pixel_active'    => $metaPixelActive,
            'ga4_active'           => $ga4Active,
            'gtm_active'           => $gtmActive,
            'active_pixels_count'  => $activePixelsCount,
            'is_maintenance'       => $isMaintenance,
            'accepting_orders'     => $acceptingOrders,
            'latest_update'        => $latestUpdate,
        ];
    }
}
