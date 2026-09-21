<?php
/**
 * JIYAJI LUXURY: SEED INITIAL SUPPORT TICKETS
 * Populates realistic client concierge inquiries across various priorities and statuses.
 */

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();
foreach ($_ENV as $k => $v) putenv("$k=$v");
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/Ticket.php';

use App\Config\Database;
use App\Models\Ticket;

$db = Database::connect();

echo "Seeding luxury support tickets...\n";

// Clear existing support messages & tickets
$db->query("DELETE FROM support_ticket_messages");
$db->query("DELETE FROM support_tickets");
$db->query("ALTER TABLE support_ticket_messages AUTO_INCREMENT = 1");
$db->query("ALTER TABLE support_tickets AUTO_INCREMENT = 1");

$sampleTickets = [
    [
        'ticket' => [
            'customer_id' => 1, // Aditya Kapoor
            'order_id'    => 1, // Order #1
            'subject'     => "Urgent: Alteration request for Raw Silk Sherwani delivery before wedding",
            'priority'    => 'critical',
            'status'      => 'open',
            'assigned_to' => 1,
        ],
        'messages' => [
            [
                'sender_type' => 'customer',
                'sender_id'   => 1,
                'message'     => "Hello Jiyaji concierge, I received Order #1 yesterday. The Raw Silk Sherwani is magnificent, but the chest taper feels slightly snug around the shoulder seams. Can I arrange an urgent alteration fitting before Saturday?"
            ],
            [
                'sender_type' => 'customer',
                'sender_id'   => 1,
                'message'     => "Also, please let me know if your master tailor can do in-house measurement adjustments in Mumbai."
            ]
        ]
    ],
    [
        'ticket' => [
            'customer_id' => 2, // Meera Vardhan
            'order_id'    => 2, // Order #2
            'subject'     => "Bespoke embroidery color customization inquiry",
            'priority'    => 'high',
            'status'      => 'in_progress',
            'assigned_to' => 1,
        ],
        'messages' => [
            [
                'sender_type' => 'customer',
                'sender_id'   => 2,
                'message'     => "Good afternoon, I am interested in ordering a customized version of the Velvet Bandhgala Tuxedo with antique gold zardozi threadwork instead of silver. Is this possible for custom bespoke tailoring?"
            ],
            [
                'sender_type' => 'admin',
                'sender_id'   => 1,
                'message'     => "Dear Meera, thank you for reaching out to Jiyaji Luxury Concierge. Absolutely, our master artisans can weave antique matte gold metallic zardozi threads on our midnight blue velvet. I have notified our senior couturier and will share sample swatch photos shortly."
            ]
        ]
    ],
    [
        'ticket' => [
            'customer_id' => 3, // Rohan Deshmukh
            'order_id'    => 3, // Order #3
            'subject'     => "Delivery destination update for Italian Handcrafted Loafers",
            'priority'    => 'medium',
            'status'      => 'acknowledged',
            'assigned_to' => null,
        ],
        'messages' => [
            [
                'sender_type' => 'customer',
                'sender_id'   => 3,
                'message'     => "Please note I will be traveling next week. Could you update the delivery destination for Order #3 to my alternate office address in BKC?"
            ]
        ]
    ],
    [
        'ticket' => [
            'customer_id' => 5, // Ananya Birla
            'order_id'    => 5, // Order #5
            'subject'     => "Care instructions for Kashmiri Pure Pashmina Shawl",
            'priority'    => 'low',
            'status'      => 'resolved',
            'assigned_to' => 1,
        ],
        'messages' => [
            [
                'sender_type' => 'customer',
                'sender_id'   => 5,
                'message'     => "Could you please advise on the recommended dry cleaning protocols and cedar wood storage box care for the Kashmiri Shawl Stole?"
            ],
            [
                'sender_type' => 'admin',
                'sender_id'   => 1,
                'message'     => "Dear Ananya, we recommend exclusively hydrocarbon dry cleaning by specialized luxury textile laundries. Keep the stole wrapped in unbleached muslin inside your complimentary cedar wood chest away from direct humidity."
            ]
        ]
    ],
];

foreach ($sampleTickets as $st) {
    $tData = $st['ticket'];
    $tId = Ticket::create($tData);
    echo "  + Inserted ticket #TKT-" . str_pad((string)$tId, 5, '0', STR_PAD_LEFT) . " ({$tData['priority']}, {$tData['status']}): {$tData['subject']}\n";

    foreach ($st['messages'] as $msg) {
        Ticket::addMessage($tId, $msg['sender_type'], $msg['sender_id'], $msg['message']);
        echo "    - Added message from {$msg['sender_type']}\n";
    }
}

$kpis = Ticket::getKPIs();
echo "\n--- SUPPORT TICKETS KPIS ---\n";
print_r($kpis);

echo "\nSupport tickets fixtures seeded successfully!\n";
