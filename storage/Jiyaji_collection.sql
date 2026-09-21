-- ============================================================
--  JIYAJI LX — E-Commerce MySQL Database Schema
--  Generated for: Jiya JI LX (Quotation No. 048, Sept 2026)
--  Tech Stack: PHP Custom MVC + MySQL
--  Covers all 30 features from the project quotation
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- ============================================================
-- 1. ROLES & ACCESS CONTROL  (Feature #29)
-- ============================================================

CREATE TABLE roles (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50)  NOT NULL UNIQUE,          -- e.g. super_admin, order_manager, inventory_manager, support_staff
    description VARCHAR(255),
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE permissions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module      VARCHAR(60)  NOT NULL,                 -- e.g. products, orders, coupons, analytics
    action      VARCHAR(30)  NOT NULL,                 -- view | create | edit | delete
    UNIQUE KEY uq_perm (module, action)
);

CREATE TABLE role_permissions (
    role_id       INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id)       REFERENCES roles(id)       ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- ============================================================
-- 2. ADMIN USERS  (Features #10, #11, #29)
-- ============================================================

CREATE TABLE admins (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id       INT UNSIGNED NOT NULL,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,               -- bcrypt
    is_active     TINYINT(1)   DEFAULT 1,
    created_by    INT UNSIGNED,                        -- super admin who created this account
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id)    REFERENCES roles(id),
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- ============================================================
-- 3. CUSTOMERS / USERS  (Features #7, #8)
-- ============================================================

CREATE TABLE customers (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(100) NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    phone             VARCHAR(15),
    password_hash     VARCHAR(255) NOT NULL,           -- bcrypt
    email_verified    TINYINT(1)   DEFAULT 0,
    is_active         TINYINT(1)   DEFAULT 1,
    profile_photo_url VARCHAR(512),
    created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE customer_sessions (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED    NOT NULL,
    token_hash  VARCHAR(255)    NOT NULL UNIQUE,       -- hashed session/cookie token
    ip_address  VARCHAR(45),
    user_agent  TEXT,
    expires_at  DATETIME        NOT NULL,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE password_resets (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED    NOT NULL,
    token_hash  VARCHAR(255)    NOT NULL,
    expires_at  DATETIME        NOT NULL,
    used        TINYINT(1)      DEFAULT 0,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- ============================================================
-- 4. CUSTOMER ADDRESSES  (Feature #8)
-- ============================================================

CREATE TABLE customer_addresses (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id  INT UNSIGNED    NOT NULL,
    label        VARCHAR(30)     DEFAULT 'Home',       -- Home | Work | Other
    recipient    VARCHAR(100)    NOT NULL,
    phone        VARCHAR(15),
    address_line1 VARCHAR(255)   NOT NULL,
    address_line2 VARCHAR(255),
    city         VARCHAR(100)    NOT NULL,
    state        VARCHAR(100)    NOT NULL,
    pincode      VARCHAR(10)     NOT NULL,
    country      VARCHAR(60)     DEFAULT 'India',
    is_default   TINYINT(1)      DEFAULT 0,
    created_at   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- ============================================================
-- 5. CATEGORIES  (Features #2, #10)
-- ============================================================

CREATE TABLE categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id   INT UNSIGNED,                          -- NULL = top-level category
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    image_url   VARCHAR(512),
    sort_order  INT          DEFAULT 0,
    is_active   TINYINT(1)  DEFAULT 1,
    created_at  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- ============================================================
-- 6. BRANDS  (Feature #9 filter)
-- ============================================================

CREATE TABLE brands (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL UNIQUE,
    slug       VARCHAR(120) NOT NULL UNIQUE,
    logo_url   VARCHAR(512),
    is_active  TINYINT(1)  DEFAULT 1,
    created_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 7. PRODUCTS  (Features #3, #4, #10)
-- ============================================================

CREATE TABLE products (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id      INT UNSIGNED    NOT NULL,
    brand_id         INT UNSIGNED,
    name             VARCHAR(255)    NOT NULL,
    slug             VARCHAR(280)    NOT NULL UNIQUE,
    short_description VARCHAR(500),
    description      LONGTEXT,                         -- rich-text HTML from editor
    base_price       DECIMAL(10,2)   NOT NULL,
    sale_price       DECIMAL(10,2),
    low_stock_threshold INT UNSIGNED DEFAULT 10,       -- Feature #25
    allow_backorder  TINYINT(1)      DEFAULT 0,        -- Feature #25
    status           ENUM('active','draft','archived') DEFAULT 'draft',
    meta_title       VARCHAR(255),
    meta_description VARCHAR(512),
    average_rating   DECIMAL(3,2)    DEFAULT 0.00,     -- denormalized, updated by trigger/cron
    review_count     INT UNSIGNED    DEFAULT 0,
    created_at       TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (brand_id)    REFERENCES brands(id) ON DELETE SET NULL
);

-- Attribute definitions: Size, Colour, Material …
CREATE TABLE attributes (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL UNIQUE                   -- e.g. Size, Colour
);

CREATE TABLE attribute_values (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attribute_id INT UNSIGNED NOT NULL,
    value        VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_attr_val (attribute_id, value),
    FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE
);

-- Product variants linked to attribute combinations  (Feature #4)
CREATE TABLE product_variants (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id     INT UNSIGNED    NOT NULL,
    sku            VARCHAR(100)    NOT NULL UNIQUE,
    price_override DECIMAL(10,2),                      -- NULL = use product base_price
    stock_qty      INT             DEFAULT 0,
    is_active      TINYINT(1)      DEFAULT 1,
    created_at     TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE variant_attribute_values (
    variant_id         INT UNSIGNED NOT NULL,
    attribute_value_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (variant_id, attribute_value_id),
    FOREIGN KEY (variant_id)         REFERENCES product_variants(id)    ON DELETE CASCADE,
    FOREIGN KEY (attribute_value_id) REFERENCES attribute_values(id)    ON DELETE CASCADE
);

-- Product images  (Feature #4)
CREATE TABLE product_images (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED    NOT NULL,
    variant_id INT UNSIGNED,                           -- NULL = applies to all variants
    url        VARCHAR(512)    NOT NULL,
    alt_text   VARCHAR(255),
    sort_order INT             DEFAULT 0,
    is_primary TINYINT(1)      DEFAULT 0,
    created_at TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
);

-- Product specification table  (Feature #4)
CREATE TABLE product_specifications (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED    NOT NULL,
    spec_key   VARCHAR(100)    NOT NULL,
    spec_value VARCHAR(255)    NOT NULL,
    sort_order INT             DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- 8. INVENTORY & STOCK ALERTS  (Feature #25)
-- ============================================================

CREATE TABLE stock_movements (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    variant_id  INT UNSIGNED    NOT NULL,
    movement    INT             NOT NULL,               -- positive=in, negative=out
    reason      ENUM('sale','return','manual_restock','cancellation','adjustment') NOT NULL,
    reference_id INT UNSIGNED,                         -- order_id / return_id
    note        VARCHAR(255),
    created_by  INT UNSIGNED,                          -- admin id or NULL for system
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)
);

CREATE TABLE stock_alerts (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    variant_id  INT UNSIGNED    NOT NULL UNIQUE,
    alerted_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    resolved    TINYINT(1)      DEFAULT 0,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)
);

-- ============================================================
-- 9. HOMEPAGE CONTENT  (Feature #2)
-- ============================================================

CREATE TABLE banners (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(150),
    image_url  VARCHAR(512)    NOT NULL,
    link_url   VARCHAR(512),
    sort_order INT             DEFAULT 0,
    is_active  TINYINT(1)      DEFAULT 1,
    created_at TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE promotional_sections (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    heading    VARCHAR(200),
    sub_heading VARCHAR(200),
    image_url  VARCHAR(512),
    link_url   VARCHAR(512),
    position   VARCHAR(60),                            -- hero_left | sidebar | footer_banner
    is_active  TINYINT(1)      DEFAULT 1,
    created_at TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 10. WISHLISTS  (Feature #6)
-- ============================================================

CREATE TABLE wishlists (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED    NOT NULL UNIQUE,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE wishlist_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wishlist_id INT UNSIGNED    NOT NULL,
    variant_id  INT UNSIGNED    NOT NULL,
    added_at    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wish_item (wishlist_id, variant_id),
    FOREIGN KEY (wishlist_id) REFERENCES wishlists(id)         ON DELETE CASCADE,
    FOREIGN KEY (variant_id)  REFERENCES product_variants(id)  ON DELETE CASCADE
);

-- ============================================================
-- 11. SHOPPING CART  (Feature #5)
-- ============================================================

CREATE TABLE carts (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED,                          -- NULL = guest cart
    session_key VARCHAR(128)    UNIQUE,                -- for guest persistence
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

CREATE TABLE cart_items (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id    INT UNSIGNED    NOT NULL,
    variant_id INT UNSIGNED    NOT NULL,
    quantity   INT UNSIGNED    NOT NULL DEFAULT 1,
    added_at   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cart_item (cart_id, variant_id),
    FOREIGN KEY (cart_id)    REFERENCES carts(id)              ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)   ON DELETE CASCADE
);

-- ============================================================
-- 12. COUPON CODES  (Feature #23)
-- ============================================================

CREATE TABLE coupons (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code                 VARCHAR(50)     NOT NULL UNIQUE,
    description          VARCHAR(255),
    type                 ENUM('flat','percentage') NOT NULL,
    value                DECIMAL(10,2)   NOT NULL,     -- amount off or % off
    max_discount_cap     DECIMAL(10,2),                -- max discount for percentage coupons
    min_cart_value       DECIMAL(10,2)   DEFAULT 0,
    usage_limit_global   INT UNSIGNED,                 -- total redemptions allowed
    usage_limit_per_user INT UNSIGNED    DEFAULT 1,
    times_used           INT UNSIGNED    DEFAULT 0,
    is_public            TINYINT(1)      DEFAULT 1,
    starts_at            DATETIME,
    expires_at           DATETIME,
    is_active            TINYINT(1)      DEFAULT 1,
    created_by           INT UNSIGNED,
    created_at           TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Category / product restrictions for a coupon
CREATE TABLE coupon_category_restrictions (
    coupon_id   INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (coupon_id, category_id),
    FOREIGN KEY (coupon_id)   REFERENCES coupons(id)     ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)  ON DELETE CASCADE
);

CREATE TABLE coupon_product_restrictions (
    coupon_id  INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (coupon_id, product_id),
    FOREIGN KEY (coupon_id)  REFERENCES coupons(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)  ON DELETE CASCADE
);

CREATE TABLE coupon_usages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id   INT UNSIGNED    NOT NULL,
    customer_id INT UNSIGNED    NOT NULL,
    order_id    INT UNSIGNED,                          -- set after order placed
    used_at     TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id)   REFERENCES coupons(id)    ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id)  ON DELETE CASCADE
);

-- ============================================================
-- 13. SHIPPING CONFIG  (Features #20, #12, #18)
-- ============================================================

CREATE TABLE shipping_zones (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    pincodes   TEXT,                                   -- CSV or JSON list of pincodes
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE shipping_rates (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    zone_id         INT UNSIGNED    NOT NULL,
    method          ENUM('standard','express','free') NOT NULL,
    weight_from_g   INT UNSIGNED    DEFAULT 0,         -- weight slab start (grams)
    weight_to_g     INT UNSIGNED,                      -- NULL = no upper limit
    flat_rate       DECIMAL(10,2)   NOT NULL,
    free_above_order_value DECIMAL(10,2),              -- Feature #20 free shipping threshold
    is_active       TINYINT(1)      DEFAULT 1,
    FOREIGN KEY (zone_id) REFERENCES shipping_zones(id) ON DELETE CASCADE
);

CREATE TABLE payment_methods_config (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    method      VARCHAR(60)     NOT NULL UNIQUE,       -- cod, razorpay, upi, netbanking
    is_enabled  TINYINT(1)      DEFAULT 1,
    cod_min_order_value DECIMAL(10,2),
    cod_pincode_whitelist TEXT,                        -- JSON array or CSV
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- 14. ORDERS  (Features #11, #12, #13)
-- ============================================================

CREATE TABLE orders (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id          INT UNSIGNED,                 -- NULL if guest
    guest_email          VARCHAR(150),
    order_number         VARCHAR(30)     NOT NULL UNIQUE,   -- e.g. JLX-20260908-0001
    status               ENUM('pending','confirmed','packed','shipped','out_for_delivery','delivered','cancelled') DEFAULT 'pending',
    -- Address snapshot (denormalized so address changes don't affect history)
    shipping_name        VARCHAR(100)    NOT NULL,
    shipping_phone       VARCHAR(15),
    shipping_address1    VARCHAR(255)    NOT NULL,
    shipping_address2    VARCHAR(255),
    shipping_city        VARCHAR(100)    NOT NULL,
    shipping_state       VARCHAR(100)    NOT NULL,
    shipping_pincode     VARCHAR(10)     NOT NULL,
    shipping_country     VARCHAR(60)     DEFAULT 'India',
    -- Pricing
    subtotal             DECIMAL(10,2)   NOT NULL,
    discount_amount      DECIMAL(10,2)   DEFAULT 0,
    coupon_id            INT UNSIGNED,
    coupon_code_used     VARCHAR(50),
    shipping_charge      DECIMAL(10,2)   DEFAULT 0,
    tax_amount           DECIMAL(10,2)   DEFAULT 0,
    grand_total          DECIMAL(10,2)   NOT NULL,
    -- Payment
    payment_method       VARCHAR(60)     NOT NULL,     -- cod, razorpay, upi …
    payment_status       ENUM('pending','paid','failed','refunded','partial_refund') DEFAULT 'pending',
    -- Courier / tracking  (Feature #18, #19)
    courier_partner      VARCHAR(60),
    awb_number           VARCHAR(100),
    shipping_label_url   VARCHAR(512),
    estimated_delivery   DATE,
    -- Metadata
    notes                TEXT,
    placed_at            TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (coupon_id)   REFERENCES coupons(id)   ON DELETE SET NULL
);

CREATE TABLE order_items (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id      INT UNSIGNED    NOT NULL,
    variant_id    INT UNSIGNED,                        -- NULL if variant deleted
    product_name  VARCHAR(255)    NOT NULL,            -- snapshot
    variant_info  VARCHAR(255),                        -- e.g. "Size: M, Colour: Red"
    sku           VARCHAR(100),
    quantity      INT UNSIGNED    NOT NULL,
    unit_price    DECIMAL(10,2)   NOT NULL,
    line_total    DECIMAL(10,2)   NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)            ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)  ON DELETE SET NULL
);

-- Order status history  (Features #11, #13)
CREATE TABLE order_status_history (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id   INT UNSIGNED    NOT NULL,
    status     VARCHAR(60)     NOT NULL,
    note       TEXT,
    changed_by INT UNSIGNED,                           -- admin id
    changed_at TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES admins(id)   ON DELETE SET NULL
);

-- ============================================================
-- 15. PAYMENTS  (Features #16, #17)
-- ============================================================

CREATE TABLE payments (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id              INT UNSIGNED    NOT NULL,
    gateway               VARCHAR(60)     NOT NULL,    -- razorpay | cod | upi
    gateway_order_id      VARCHAR(150),
    gateway_payment_id    VARCHAR(150),
    gateway_signature     VARCHAR(255),
    amount                DECIMAL(10,2)   NOT NULL,
    currency              CHAR(3)         DEFAULT 'INR',
    status                ENUM('initiated','success','failed','refunded','partial_refund') DEFAULT 'initiated',
    webhook_payload       JSON,
    created_at            TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- ============================================================
-- 16. RETURNS & REFUNDS  (Features #21, #22)
-- ============================================================

CREATE TABLE returns (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        INT UNSIGNED    NOT NULL,
    customer_id     INT UNSIGNED,
    reason          VARCHAR(255)    NOT NULL,
    description     TEXT,
    status          ENUM('requested','approved','rejected','pickup_scheduled','item_received','refund_initiated','completed') DEFAULT 'requested',
    photo_urls      JSON,                              -- array of uploaded photo URLs
    admin_note      TEXT,
    reverse_awb     VARCHAR(100),                      -- reverse pickup tracking number
    refund_amount   DECIMAL(10,2),
    refund_method   VARCHAR(60),                       -- original_payment | wallet | bank
    gateway_refund_id VARCHAR(150),
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id)    REFERENCES orders(id)    ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

CREATE TABLE return_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_id   INT UNSIGNED    NOT NULL,
    order_item_id INT UNSIGNED  NOT NULL,
    quantity    INT UNSIGNED    NOT NULL DEFAULT 1,
    FOREIGN KEY (return_id)    REFERENCES returns(id)     ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE
);

-- ============================================================
-- 17. CUSTOMER REVIEWS & RATINGS  (Feature #24)
-- ============================================================

CREATE TABLE reviews (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id   INT UNSIGNED    NOT NULL,
    customer_id  INT UNSIGNED    NOT NULL,
    order_item_id INT UNSIGNED,                        -- verified buyer link
    rating       TINYINT UNSIGNED NOT NULL,            -- 1-5
    title        VARCHAR(150),
    body         TEXT,
    photo_urls   JSON,
    status       ENUM('pending','approved','rejected','flagged') DEFAULT 'pending',
    moderated_by INT UNSIGNED,
    moderated_at DATETIME,
    created_at   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_review (product_id, customer_id, order_item_id),
    FOREIGN KEY (product_id)    REFERENCES products(id)   ON DELETE CASCADE,
    FOREIGN KEY (customer_id)   REFERENCES customers(id)  ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE SET NULL,
    FOREIGN KEY (moderated_by)  REFERENCES admins(id)     ON DELETE SET NULL
);

-- ============================================================
-- 18. SEARCH & KEYWORD TRACKING  (Features #9, #26)
-- ============================================================

CREATE TABLE search_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED,
    keyword     VARCHAR(255)    NOT NULL,
    results_count INT UNSIGNED  DEFAULT 0,
    searched_at TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- ============================================================
-- 19. NOTIFICATIONS  (Feature #27)
-- ============================================================

CREATE TABLE notification_templates (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trigger   VARCHAR(60) NOT NULL UNIQUE,             -- order_placed | payment_confirmed | order_shipped …
    channel   ENUM('email','sms','whatsapp') NOT NULL,
    subject   VARCHAR(255),
    body      TEXT        NOT NULL,
    is_active TINYINT(1)  DEFAULT 1
);

CREATE TABLE notification_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED,
    channel     ENUM('email','sms','whatsapp') NOT NULL,
    `trigger`   VARCHAR(60),
    recipient   VARCHAR(150)    NOT NULL,
    subject     VARCHAR(255),
    body        TEXT,
    status      ENUM('queued','sent','failed') DEFAULT 'queued',
    sent_at     TIMESTAMP,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- ============================================================
-- 20. ANALYTICS & REPORTING  (Feature #26)
-- ============================================================

CREATE TABLE analytics_events (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type  VARCHAR(60)     NOT NULL,              -- page_view | add_to_cart | purchase | search
    customer_id INT UNSIGNED,
    session_id  VARCHAR(128),
    product_id  INT UNSIGNED,
    category_id INT UNSIGNED,
    order_id    INT UNSIGNED,
    meta        JSON,                                  -- flexible additional data
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id)  REFERENCES products(id)  ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id)    REFERENCES orders(id)    ON DELETE SET NULL
);

-- Pre-aggregated daily sales for fast dashboard queries
CREATE TABLE daily_sales_summary (
    summary_date   DATE         NOT NULL PRIMARY KEY,
    total_orders   INT UNSIGNED DEFAULT 0,
    total_revenue  DECIMAL(14,2) DEFAULT 0,
    total_discount DECIMAL(14,2) DEFAULT 0,
    avg_order_value DECIMAL(10,2) DEFAULT 0,
    new_customers  INT UNSIGNED DEFAULT 0,
    updated_at     TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- 21. STATIC CONTENT PAGES  (Feature #14)
-- ============================================================

CREATE TABLE static_pages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug       VARCHAR(100) NOT NULL UNIQUE,           -- contact-us | about-us | privacy-policy …
    title      VARCHAR(200) NOT NULL,
    content    LONGTEXT,
    is_active  TINYINT(1)  DEFAULT 1,
    updated_by INT UNSIGNED,
    updated_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Contact/callback enquiries from Contact Us page  (Feature #14)
CREATE TABLE contact_enquiries (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150),
    phone      VARCHAR(15),
    subject    VARCHAR(255),
    message    TEXT         NOT NULL,
    is_read    TINYINT(1)  DEFAULT 0,
    created_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 22. SITE SETTINGS  (WhatsApp button, Meta Pixel, etc.)
--     (Features #26, #30)
-- ============================================================

CREATE TABLE site_settings (
    setting_key   VARCHAR(100) NOT NULL PRIMARY KEY,   -- e.g. whatsapp_number | meta_pixel_id | razorpay_key_id
    setting_value TEXT,
    label         VARCHAR(200),
    updated_by    INT UNSIGNED,
    updated_at    TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- ============================================================
-- 23. SUPPORT TICKETS  (Feature #28)
-- ============================================================

CREATE TABLE support_tickets (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id  INT UNSIGNED,
    order_id     INT UNSIGNED,
    subject      VARCHAR(255)    NOT NULL,
    priority     ENUM('low','medium','high','critical') DEFAULT 'medium',
    status       ENUM('open','acknowledged','in_progress','resolved','closed') DEFAULT 'open',
    assigned_to  INT UNSIGNED,                         -- admin id
    created_at   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id)    REFERENCES orders(id)    ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES admins(id)    ON DELETE SET NULL
);

CREATE TABLE support_ticket_messages (
    id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED    NOT NULL,
    sender_type ENUM('customer','admin') NOT NULL,
    sender_id INT UNSIGNED    NOT NULL,
    message   TEXT            NOT NULL,
    created_at TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE
);

-- ============================================================
-- 24. SEED DATA — Essential defaults
-- ============================================================

INSERT INTO roles (name, description) VALUES
('super_admin',        'Full access to all modules'),
('order_manager',      'View and manage orders'),
('inventory_manager',  'Manage products and stock'),
('support_staff',      'Handle support tickets and returns');

INSERT INTO permissions (module, action) VALUES
('products',   'view'),  ('products',   'create'), ('products',   'edit'),  ('products',   'delete'),
('orders',     'view'),  ('orders',     'edit'),
('inventory',  'view'),  ('inventory',  'edit'),
('coupons',    'view'),  ('coupons',    'create'), ('coupons',    'edit'),  ('coupons',    'delete'),
('analytics',  'view'),
('customers',  'view'),
('returns',    'view'),  ('returns',    'edit'),
('settings',   'view'),  ('settings',   'edit'),
('admins',     'view'),  ('admins',     'create'), ('admins',     'edit'),  ('admins',     'delete'),
('pages',      'view'),  ('pages',      'edit'),
('reviews',    'view'),  ('reviews',    'edit'),
('support',    'view'),  ('support',    'edit');

INSERT INTO site_settings (setting_key, label, setting_value) VALUES
('store_name',              'Store Name',                    'Jiya JI LX'),
('store_email',             'Store Email',                   ''),
('store_phone',             'Store Phone',                   ''),
('whatsapp_number',         'WhatsApp Chat Number',          ''),
('razorpay_key_id',         'Razorpay Key ID',               ''),
('razorpay_key_secret',     'Razorpay Key Secret',           ''),
('shiprocket_email',        'Shiprocket Login Email',        ''),
('shiprocket_password',     'Shiprocket Password',           ''),
('meta_pixel_id',           'Meta Pixel ID',                 ''),
('free_shipping_threshold', 'Free Shipping Above (₹)',       '999'),
('cod_enabled',             'COD Enabled',                   '1'),
('low_stock_default',       'Default Low-Stock Threshold',   '10'),
('currency_symbol',         'Currency Symbol',               '₹');

INSERT INTO notification_templates (trigger, channel, subject, body) VALUES
('order_placed',      'email', 'Order Confirmed – {{order_number}}', 'Hi {{customer_name}}, your order {{order_number}} has been placed successfully.'),
('payment_confirmed', 'email', 'Payment Received – {{order_number}}', 'We have received your payment of ₹{{amount}} for order {{order_number}}.'),
('order_shipped',     'email', 'Your Order is on the Way! – {{order_number}}', 'Great news! Your order {{order_number}} has been shipped. Track it here: {{tracking_link}}.'),
('out_for_delivery',  'email', 'Out for Delivery – {{order_number}}', 'Your order {{order_number}} is out for delivery today.'),
('order_delivered',   'email', 'Delivered – {{order_number}}', 'Your order {{order_number}} has been delivered. Enjoy your purchase!'),
('order_cancelled',   'email', 'Order Cancelled – {{order_number}}', 'Your order {{order_number}} has been cancelled.'),
('return_approved',   'email', 'Return Approved – {{order_number}}', 'Your return request for order {{order_number}} has been approved.'),
('return_rejected',   'email', 'Return Update – {{order_number}}', 'Your return request for order {{order_number}} could not be approved.'),
('refund_processed',  'email', 'Refund Initiated – {{order_number}}', 'Your refund of ₹{{refund_amount}} for order {{order_number}} has been initiated.');

INSERT INTO static_pages (slug, title, content) VALUES
('about-us',       'About Us',       '<p>Welcome to Jiya JI LX.</p>'),
('contact-us',     'Contact Us',     '<p>Get in touch with us.</p>'),
('privacy-policy', 'Privacy Policy', '<p>Your privacy matters to us.</p>'),
('terms',          'Terms & Conditions', '<p>Please read our terms carefully.</p>'),
('shipping-policy','Shipping Policy','<p>We ship all over India.</p>');

-- ============================================================
-- 25. USEFUL INDEXES FOR PERFORMANCE
-- ============================================================

CREATE INDEX idx_products_category   ON products (category_id);
CREATE INDEX idx_products_status     ON products (status);
CREATE INDEX idx_products_slug       ON products (slug);
CREATE INDEX idx_variants_product    ON product_variants (product_id);
CREATE INDEX idx_orders_customer     ON orders (customer_id);
CREATE INDEX idx_orders_status       ON orders (status);
CREATE INDEX idx_orders_placed_at    ON orders (placed_at);
CREATE INDEX idx_order_items_order   ON order_items (order_id);
CREATE INDEX idx_payments_order      ON payments (order_id);
CREATE INDEX idx_returns_order       ON returns (order_id);
CREATE INDEX idx_reviews_product     ON reviews (product_id, status);
CREATE INDEX idx_coupons_code        ON coupons (code, is_active);
CREATE INDEX idx_search_logs_keyword ON search_logs (keyword);
CREATE INDEX idx_analytics_type_date ON analytics_events (event_type, created_at);
CREATE INDEX idx_stock_movements_var ON stock_movements (variant_id);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF SCHEMA
-- Total tables: 46
-- ============================================================
