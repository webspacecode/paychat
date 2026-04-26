-- =========================================
-- 🔧 SELECT DATABASE
-- =========================================
USE paychat;


-- =========================================
-- 🔧 VARIABLES (EDIT THESE PER CLIENT)
-- =========================================
SET @tenant_id = 38;
SET @gst_number = '27ABCDE1234F1Z5';
SET @company_name = 'Coffee Latte';
SET @logo_url = 'https://dummyimage.com/200x60/000/fff&text=Coffee+Latte';
SET @primary_color = '#1F2937';
SET @phone = '9021345678';
SET @address = '2nd Floor, Food Court, Eternity Mall, Nagpur';

SET @upi_id = '9834969229@ybl';
SET @upi_name = 'Coffee Latte';


-- =========================================
-- 🧾 TAX CONFIG (SAFE INSERT)
-- =========================================
INSERT INTO tax_configs (
    tenant_id,
    gst_number,
    is_gst_enabled,
    is_inclusive,
    cgst_rate,
    sgst_rate,
    igst_rate,
    created_at,
    updated_at
) VALUES (
    @tenant_id,
    @gst_number,
    1,
    0,
    9.00,
    9.00,
    18.00,
    NOW(),
    NOW()
);


-- =========================================
-- 🎨 BRANDING
-- =========================================
INSERT INTO brandings (
    tenant_id,
    company_name,
    logo,
    primary_color,
    phone,
    address,
    created_at,
    updated_at
) VALUES (
    @tenant_id,
    @company_name,
    @logo_url,
    @primary_color,
    @phone,
    @address,
    NOW(),
    NOW()
);


-- =========================================
-- 🔄 ENSURE DB CONTEXT AGAIN (AS REQUESTED)
-- =========================================
USE tenant_cafe_one;


-- =========================================
-- 💳 PAYMENT METHODS
-- =========================================
INSERT INTO payment_methods (
    type,
    mode,
    enabled,
    config,
    created_at,
    updated_at
) VALUES
('cash', NULL, 1, NULL, NOW(), NOW()),

('upi', 'personal', 1,
 JSON_OBJECT(
    'upi_id', @upi_id,
    'name', @upi_name
 ),
 NOW(), NOW()),

('upi', 'business', 0,
 JSON_OBJECT(
    'provider', 'phonepe',
    'merchant_id', 'TEST_MERCHANT_001'
 ),
 NOW(), NOW()),

('gateway', NULL, 0,
 JSON_OBJECT(
    'provider', 'razorpay',
    'key', 'rzp_test_xxxxx',
    'secret', 'xxxxxx'
 ),
 NOW(), NOW());


-- =========================================
-- ⚙️ OPTIONAL SETTINGS (COMMENT IF NOT NEEDED)
-- =========================================
-- INSERT INTO settings (setting_key, value, type) VALUES
-- ('token_system_enabled', 'true', 'boolean'),
-- ('token_prefix', 'A', 'string'),
-- ('token_start_number', '100', 'string'),
-- ('token_reset_daily', 'true', 'boolean');