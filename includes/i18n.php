<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function dnm_load_textdomain(): void {
    load_plugin_textdomain( 'eweb-davion-ny-meetings', false, dirname( plugin_basename( DNM_FILE ) ) . '/languages' );
}

function dnm_current_lang(): string {
    if ( function_exists( 'pll_current_language' ) ) {
        $lang = pll_current_language( 'slug' );
        if ( is_string( $lang ) && '' !== $lang ) {
            return $lang;
        }
    }
    return 'pt';
}

function dnm_tr( array $map ): string {
    $lang = dnm_current_lang();
    if ( isset( $map[ $lang ] ) ) {
        return (string) $map[ $lang ];
    }
    return (string) ( $map['en'] ?? reset( $map ) );
}
