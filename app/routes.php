<?php

require_once __DIR__ . '/Router.php';

$router = new Router();

// =============================================================================
// PUBLIC ROUTES (No Authentication Required)
// =============================================================================

$router->get('/', 'HomeController@index');
$router->get('home', 'HomeController@index');

// =============================================================================
// STATIC BRAND & COMPLIANCE PAGES (Feature 14)
// =============================================================================
$router->get('about-us', 'Admin/PageController@aboutUs');
$router->get('privacy-policy', 'Admin/PageController@privacyPolicy');
$router->get('terms-conditions', 'Admin/PageController@termsConditions');
$router->get('shipping-policy', 'Admin/PageController@shippingPolicy');
$router->get('returns-policy', 'Admin/PageController@returnsPolicy');
$router->get('faq', 'Admin/PageController@faq');
$router->get('contact-us', 'Admin/PageController@contactUs');
$router->post('contact-us/submit', 'Admin/PageController@submitContact');
$router->get('page/{slug}', 'Admin/PageController@showPublicPage');

// =============================================================================
// ADMIN AUTHENTICATION ROUTES
// =============================================================================

$router->get('admin', 'Admin/AuthController@showLogin');
$router->get('admin/login', 'Admin/AuthController@showLogin');
$router->post('admin/login', 'Admin/AuthController@login');
$router->get('admin/logout', 'Admin/AuthController@logout');
$router->post('admin/logout', 'Admin/AuthController@logout');

// =============================================================================
// PROTECTED ADMIN ROUTES (Authentication Required)
// =============================================================================

$router->group(fn() => \App\Middleware\AuthMiddleware::check(), function() use ($router) {
    $router->get('admin/dashboard', 'Admin/DashboardController@index');
    $router->get('Admin/Dashboard', 'Admin/DashboardController@index');

    // =========================================================================
    // ORDER MANAGEMENT ROUTES (Universal Encrypted IDs)
    // =========================================================================
    $router->get('admin/orders', 'Admin/OrderController@index');
    $router->get('admin/orders/export', 'Admin/OrderController@export');
    $router->get('admin/orders/{id}/invoice', 'Admin/OrderController@invoice');
    $router->get('admin/orders/{id}/shipping-label', 'Admin/OrderController@shippingLabel');
    $router->post('admin/orders/{id}/status', 'Admin/OrderController@updateStatus');
    $router->post('admin/orders/{id}/payment', 'Admin/OrderController@updatePayment');
    $router->post('admin/orders/{id}/tracking', 'Admin/OrderController@updateTracking');
    $router->get('admin/orders/{id}', 'Admin/OrderController@show');

    // =========================================================================
    // RETURNS & REFUND MANAGEMENT ROUTES (Universal Encrypted IDs)
    // =========================================================================
    $router->get('admin/returns', 'Admin/OrderController@returns');
    $router->post('admin/returns/{id}/status', 'Admin/OrderController@updateReturn');

    // =========================================================================
    // SHIPMENTS & AWB LOGISTICS ROUTES (Universal Encrypted IDs)
    // =========================================================================
    $router->get('admin/shipments', 'Admin/ShipmentController@index');
    $router->post('admin/shipments/create', 'Admin/ShipmentController@create');
    $router->get('admin/shipments/{id}/track', 'Admin/ShipmentController@track');
    $router->post('admin/shipments/{id}/milestone', 'Admin/ShipmentController@addMilestone');

    // =========================================================================
    // PRODUCTS & SKUs INVENTORY MANAGEMENT ROUTES (Universal Encrypted IDs)
    // =========================================================================
    $router->get('admin/products', 'Admin/ProductController@index');
    $router->get('admin/products/create', 'Admin/ProductController@create');
    $router->post('admin/products/store', 'Admin/ProductController@store');
    $router->get('admin/products/{id}/edit', 'Admin/ProductController@edit');
    $router->post('admin/products/{id}/update', 'Admin/ProductController@update');
    $router->post('admin/products/{id}/status', 'Admin/ProductController@toggleStatus');
    $router->post('admin/products/{id}/stock', 'Admin/ProductController@adjustStock');
    $router->post('admin/products/{id}/delete', 'Admin/ProductController@destroy');

    // =========================================================================
    // CATEGORY TAXONOMY MANAGEMENT ROUTES (Universal Encrypted IDs)
    // =========================================================================
    $router->get('admin/categories', 'Admin/CategoryController@index');
    $router->get('admin/categories/create', 'Admin/CategoryController@create');
    $router->post('admin/categories/store', 'Admin/CategoryController@store');
    $router->get('admin/categories/{id}/edit', 'Admin/CategoryController@edit');
    $router->post('admin/categories/{id}/update', 'Admin/CategoryController@update');
    $router->post('admin/categories/{id}/status', 'Admin/CategoryController@toggleStatus');
    $router->post('admin/categories/{id}/delete', 'Admin/CategoryController@destroy');

    // =========================================================================
    // CUSTOMER MANAGEMENT & PROFILES ROUTES (Universal Encrypted IDs)
    // =========================================================================
    $router->get('admin/customers', 'Admin/CustomerController@index');
    $router->get('admin/customers/create', 'Admin/CustomerController@create');
    $router->post('admin/customers/store', 'Admin/CustomerController@store');
    $router->get('admin/customers/{id}', 'Admin/CustomerController@show');
    $router->get('admin/customers/{id}/edit', 'Admin/CustomerController@edit');
    $router->post('admin/customers/{id}/update', 'Admin/CustomerController@update');
    $router->post('admin/customers/{id}/status', 'Admin/CustomerController@toggleStatus');
    $router->post('admin/customers/{id}/delete', 'Admin/CustomerController@destroy');
    $router->post('admin/customers/{id}/address', 'Admin/CustomerController@storeAddress');
    $router->post('admin/customers/{id}/address/{address_id}/delete', 'Admin/CustomerController@deleteAddress');
    $router->post('admin/customers/{id}/address/{address_id}/default', 'Admin/CustomerController@setDefaultAddress');

    // =========================================================================
    // REVIEWS MODERATION QUEUE ROUTES (Universal Encrypted IDs)
    // =========================================================================
    $router->get('admin/reviews', 'Admin/ReviewController@index');
    $router->post('admin/reviews/bulk', 'Admin/ReviewController@bulkModerate');
    $router->get('admin/reviews/{id}', 'Admin/ReviewController@show');
    $router->post('admin/reviews/{id}/status', 'Admin/ReviewController@moderate');
    $router->post('admin/reviews/{id}/delete', 'Admin/ReviewController@destroy');

    // =========================================================================
    // SUPPORT TICKETS & PRIORITY RESOLUTION ROUTES (Universal Encrypted IDs)
    // =========================================================================
    $router->get('admin/tickets', 'Admin/TicketController@index');
    $router->get('admin/tickets/create', 'Admin/TicketController@create');
    $router->post('admin/tickets/store', 'Admin/TicketController@store');
    $router->get('admin/tickets/customer-orders/{customer_id}', 'Admin/TicketController@customerOrders');
    $router->get('admin/tickets/{id}', 'Admin/TicketController@show');
    $router->post('admin/tickets/{id}/reply', 'Admin/TicketController@reply');
    $router->post('admin/tickets/{id}/status', 'Admin/TicketController@updateStatus');
    $router->post('admin/tickets/{id}/priority', 'Admin/TicketController@updatePriority');
    $router->post('admin/tickets/{id}/assign', 'Admin/TicketController@assign');
    $router->post('admin/tickets/{id}/delete', 'Admin/TicketController@destroy');

    // =========================================================================
    // COUPONS & PROMOTIONS ROUTES (Universal Encrypted IDs)
    // =========================================================================
    $router->get('admin/coupons', 'Admin/CouponController@index');
    $router->get('admin/coupons/create', 'Admin/CouponController@create');
    $router->post('admin/coupons/store', 'Admin/CouponController@store');
    $router->get('admin/coupons/generate-code', 'Admin/CouponController@generateCode');
    $router->get('admin/coupons/{id}', 'Admin/CouponController@show');
    $router->get('admin/coupons/{id}/edit', 'Admin/CouponController@edit');
    $router->post('admin/coupons/{id}/update', 'Admin/CouponController@update');
    $router->post('admin/coupons/{id}/status', 'Admin/CouponController@toggleStatus');
    $router->post('admin/coupons/{id}/delete', 'Admin/CouponController@destroy');

    // =========================================================================
    // SEARCH KEYWORD ANALYTICS & ZERO RESULTS INTELLIGENCE (Feature 26)
    // =========================================================================
    $router->get('admin/search-analytics', 'Admin/SearchAnalyticsController@index');
    $router->get('admin/search-analytics/term/{id}', 'Admin/SearchAnalyticsController@show');
    $router->get('admin/search-analytics/export', 'Admin/SearchAnalyticsController@export');
    $router->post('admin/search-analytics/simulate', 'Admin/SearchAnalyticsController@simulate');
    $router->get('admin/search-analytics/simulate', 'Admin/SearchAnalyticsController@simulate');
    $router->post('admin/search-analytics/delete', 'Admin/SearchAnalyticsController@destroy');
    $router->post('admin/search-analytics/clear-old', 'Admin/SearchAnalyticsController@clearOld');

    // =========================================================================
    // SALES ANALYTICS & FINANCIAL REPORTING (Feature 26)
    // =========================================================================
    $router->get('admin/sales-analytics', 'Admin/SalesAnalyticsController@index');
    $router->get('admin/sales-analytics/export', 'Admin/SalesAnalyticsController@export');
    $router->post('admin/sales-analytics/sync', 'Admin/SalesAnalyticsController@syncSummary');

    // =========================================================================
    // PAYMENT GATEWAYS & COD RULES (Features 16 & 17)
    // =========================================================================
    $router->get('admin/payment-gateways', 'Admin/PaymentGatewayController@index');
    $router->post('admin/payment-gateways/update', 'Admin/PaymentGatewayController@update');
    $router->post('admin/payment-gateways/toggle', 'Admin/PaymentGatewayController@toggle');
    $router->post('admin/payment-gateways/check-pincode', 'Admin/PaymentGatewayController@checkPincode');
    $router->get('admin/payment-gateways/check-pincode', 'Admin/PaymentGatewayController@checkPincode');
    $router->post('admin/payment-gateways/test-connection', 'Admin/PaymentGatewayController@testConnection');

    // =========================================================================
    // SHIPPING ZONES, WEIGHT SLABS & PINCODES (Features 18 & 20)
    // =========================================================================
    $router->get('admin/shipping-pincodes', 'Admin/ShippingController@index');
    $router->post('admin/shipping-pincodes/zone/save', 'Admin/ShippingController@saveZone');
    $router->post('admin/shipping-pincodes/zone/delete', 'Admin/ShippingController@deleteZone');
    $router->post('admin/shipping-pincodes/zone/toggle', 'Admin/ShippingController@toggleZone');
    $router->post('admin/shipping-pincodes/rate/save', 'Admin/ShippingController@saveRate');
    $router->post('admin/shipping-pincodes/rate/delete', 'Admin/ShippingController@deleteRate');
    $router->post('admin/shipping-pincodes/rate/toggle', 'Admin/ShippingController@toggleRate');
    $router->post('admin/shipping-pincodes/settings', 'Admin/ShippingController@saveSettings');
    $router->post('admin/shipping-pincodes/courier/save', 'Admin/ShippingController@saveCourier');
    $router->post('admin/shipping-pincodes/calculate', 'Admin/ShippingController@calculate');
    // =========================================================================
    // STAFF ACCOUNTS & ROLE-BASED ACCESS CONTROL (RBAC) (Feature 29)
    // =========================================================================
    $router->get('admin/staff', 'Admin/StaffController@index');
    $router->post('admin/staff/save', 'Admin/StaffController@saveStaff');
    $router->post('admin/staff/delete', 'Admin/StaffController@deleteStaff');
    $router->post('admin/staff/toggle', 'Admin/StaffController@toggleStaff');
    $router->post('admin/staff/role/save', 'Admin/StaffController@saveRole');
    $router->post('admin/staff/role/delete', 'Admin/StaffController@deleteRole');
    $router->get('admin/staff/check-permission', 'Admin/StaffController@checkPermission');

    // =========================================================================
    // STATIC CMS PAGES & BRAND CONTENT (Feature 14)
    // =========================================================================
    $router->get('admin/pages', 'Admin/PageController@index');
    $router->post('admin/pages/save', 'Admin/PageController@save');
    $router->post('admin/pages/toggle', 'Admin/PageController@toggle');
    $router->post('admin/pages/delete', 'Admin/PageController@delete');
    $router->post('admin/pages/enquiry/toggle', 'Admin/PageController@toggleEnquiry');
    $router->post('admin/pages/enquiry/delete', 'Admin/PageController@deleteEnquiry');
});

// =============================================================================
// RESOLVE ROUTE
// =============================================================================
$router->resolve();
