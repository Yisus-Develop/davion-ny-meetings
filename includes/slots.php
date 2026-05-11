<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function dnm_allowed_slots(): array {
    $slots = array();
    $days = array(
        '2026-07-21' => array( '09:00', '17:00' ),
        '2026-07-22' => array( '09:00', '17:00' ),
        '2026-07-23' => array( '09:00', '15:00' ),
    );

    $tz = new DateTimeZone( DNM_TZ );
    foreach ( $days as $day => $range ) {
        $start = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $day . ' ' . $range[0], $tz );
        $end = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $day . ' ' . $range[1], $tz );
        if ( ! $start || ! $end ) {
            continue;
        }
        for ( $time = $start; $time < $end; $time = $time->modify( '+30 minutes' ) ) {
            $key = $time->format( 'Y-m-d H:i:s' );
            $slots[ $key ] = $time->format( 'H:i' );
        }
    }

    return $slots;
}

function dnm_group_slots_by_day( array $available ): array {
    $grouped = array();
    foreach ( $available as $value => $label ) {
        $day = substr( $value, 0, 10 );
        if ( ! isset( $grouped[ $day ] ) ) {
            $grouped[ $day ] = array();
        }
        $grouped[ $day ][] = array(
            'value' => $value,
            'label' => $label,
        );
    }
    return $grouped;
}
