<?php
/**
 * JIYAJI LUXURY: SEED INITIAL REVIEWS
 * Populates realistic reviews across pending, approved, flagged, and rejected statuses.
 */

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();
foreach ($_ENV as $k => $v) putenv("$k=$v");
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/Review.php';

use App\Config\Database;
use App\Models\Review;

$db = Database::connect();

echo "Seeding luxury garment reviews...\n";

// Clear existing reviews
$db->query("TRUNCATE TABLE reviews");

$sampleReviews = [
    [
        'product_id' => 1, // Imperial Raw Silk Sherwani
        'customer_id' => 1, // Aditya Kapoor
        'order_item_id' => 1,
        'rating' => 5,
        'title' => "Exquisite craftsmanship, fit for royal nuptials",
        'body' => "The raw silk texture and zardozi threadwork exceeded every expectation. Wore this to my reception and received countless compliments on the drape and heritage finish.",
        'photo_urls' => ['uploads/products/sherwani_sample.jpg'],
        'status' => 'pending',
        'moderated_by' => null,
    ],
    [
        'product_id' => 2, // Regal Velvet Bandhgala Tuxedo
        'customer_id' => 2, // Meera Vardhan
        'order_item_id' => 2,
        'rating' => 5,
        'title' => "Unparalleled velvet luxury & tailored silhouette",
        'body' => "The midnight blue velvet catches the ballroom lighting magnificently. Pristine hand-stitching along the lapels and structured shoulder pad construction.",
        'photo_urls' => ['uploads/products/bandhgala_sample.jpg'],
        'status' => 'approved',
        'moderated_by' => 1,
    ],
    [
        'product_id' => 3, // Italian Handcrafted Leather Loafers
        'customer_id' => 3, // Rohan Deshmukh
        'order_item_id' => 3,
        'rating' => 4,
        'title' => "Supple calfskin leather and comfortable arch support",
        'body' => "Very refined design. Took a couple of days to break in the heel counter, but now they fit like a bespoke glove. Outstanding leather patina.",
        'photo_urls' => null,
        'status' => 'approved',
        'moderated_by' => 1,
    ],
    [
        'product_id' => 5, // Chanderi Silk Festive Kurta
        'customer_id' => 4, // Siddharth Rao
        'order_item_id' => 5,
        'rating' => 2,
        'title' => "Color tone slightly differs from studio lighting",
        'body' => "The fabric quality is fine, but the mustard undertone is much more vibrant in person than the soft champagne gold depicted on the storefront product gallery.",
        'photo_urls' => null,
        'status' => 'flagged',
        'moderated_by' => null,
    ],
    [
        'product_id' => 4, // Embroidered Kashmiri Shawl Stole
        'customer_id' => 5, // Ananya Birla
        'order_item_id' => 6,
        'rating' => 5,
        'title' => "Pure ethereal warmth - authentic Kashmiri weaving",
        'body' => "Extremely lightweight yet provides generous thermal insulation. The aari embroidery along the border has heirloom quality. Delivered in a signature velvet box.",
        'photo_urls' => ['uploads/products/shawl_detail.jpg'],
        'status' => 'approved',
        'moderated_by' => 1,
    ],
    [
        'product_id' => 7, // Zardozi Embroidered Jodhpuris
        'customer_id' => 6, // Vihaan
        'order_item_id' => 8,
        'rating' => 1,
        'title' => "Spam / irrelevant inquiry submission",
        'body' => "Can you deliver this to London by Tuesday? Please call me on my WhatsApp 9999999999.",
        'photo_urls' => null,
        'status' => 'rejected',
        'moderated_by' => 1,
    ],
    [
        'product_id' => 1, // Imperial Raw Silk Sherwani
        'customer_id' => 2, // Meera Vardhan
        'order_item_id' => null,
        'rating' => 4,
        'title' => "Impressed with packaging and delivery speed",
        'body' => "Gifted this to my brother. The custom wooden hanger and luxury dust bag made an unforgettable impression upon unboxing.",
        'photo_urls' => null,
        'status' => 'pending',
        'moderated_by' => null,
    ],
];

$productIdsToSync = [];

foreach ($sampleReviews as $rev) {
    $newId = Review::create($rev);
    echo "  + Inserted review #{$newId} (Rating: {$rev['rating']}★, Status: {$rev['status']}) for Product #{$rev['product_id']}\n";
    $productIdsToSync[$rev['product_id']] = true;
}

// Sync all affected products
foreach (array_keys($productIdsToSync) as $pid) {
    Review::syncProductRating($pid);
}

// Check KPIs
$kpis = Review::getKPIs();
echo "\n--- MODERATION KPIS ---\n";
print_r($kpis);

echo "\nInitial reviews seeded successfully!\n";
