<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function dnm_activate(): void {
    global $wpdb;
    $table_name = $wpdb->prefix . DNM_TABLE;

    $sql = "CREATE TABLE {$table_name} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        full_name VARCHAR(190) NOT NULL,
        email VARCHAR(190) NOT NULL,
        phone VARCHAR(100) NOT NULL,
        business_name VARCHAR(190) NOT NULL,
        location_country VARCHAR(190) NOT NULL,
        market_segments VARCHAR(80) NOT NULL,
        target_genders VARCHAR(80) NOT NULL,
        slot_datetime DATETIME NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY unique_slot (slot_datetime)
    ) {$wpdb->get_charset_collate()};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

function dnm_booked_slots(): array {
    global $wpdb;
    $rows = $wpdb->get_col( 'SELECT slot_datetime FROM ' . $wpdb->prefix . DNM_TABLE ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    return array_map( 'strval', (array) $rows );
}
