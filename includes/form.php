<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function dnm_render_shortcode(): string {
    if ( ! defined( 'DONOTCACHEPAGE' ) ) {
        define( 'DONOTCACHEPAGE', true );
    }
    nocache_headers();

    wp_enqueue_style( 'dnm-form' );
    wp_enqueue_script( 'dnm-form' );

    $notice = '';
    $notice_cls = '';
    $is_success = false;

    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && dnm_is_real_form_post() ) {
        $result = dnm_handle_submission();
        $notice = $result['message'] ?? '';
        $is_success = ! empty( $result['ok'] );
        $notice_cls = $is_success ? 'dnm-success' : 'dnm-error';
    }

    $all = dnm_allowed_slots();
    $booked = array_flip( dnm_booked_slots() );
    $available = array();
    foreach ( $all as $k => $v ) {
        if ( ! isset( $booked[ $k ] ) ) {
            $available[ $k ] = $v;
        }
    }
    $grouped = dnm_group_slots_by_day( $available );

    $day_labels = array(
        '2026-07-21' => dnm_tr( array( 'pt' => '21 Jul', 'es' => '21 Jul', 'en' => '21 Jul', 'fr' => '21 Juil' ) ),
        '2026-07-22' => dnm_tr( array( 'pt' => '22 Jul', 'es' => '22 Jul', 'en' => '22 Jul', 'fr' => '22 Juil' ) ),
        '2026-07-23' => dnm_tr( array( 'pt' => '23 Jul', 'es' => '23 Jul', 'en' => '23 Jul', 'fr' => '23 Juil' ) ),
    );

    ob_start();
    ?>
    <div class="dnm-wrap">
        <?php if ( $notice ) : ?><div class="dnm-note <?php echo esc_attr( $notice_cls ); ?>"><?php echo esc_html( $notice ); ?></div><?php endif; ?>
        <form method="post">
            <?php wp_nonce_field( 'dnm_book_meeting', 'dnm_nonce' ); ?>
            <input type="hidden" name="dnm_form_ts" value="<?php echo esc_attr( (string) time() ); ?>">
            <div style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">
                <label for="dnm_website">Website</label>
                <input id="dnm_website" name="website" type="text" tabindex="-1" autocomplete="off">
            </div>
            <div class="dnm-grid">
                <div><label for="dnm_full_name"><?php echo esc_html( dnm_text( 'label_full_name', dnm_tr( array( 'pt' => 'Nome e Apelido', 'es' => 'Nombre y Apellido', 'en' => 'First and Last Name', 'fr' => 'Nom et Prénom' ) ) ) ); ?></label><input id="dnm_full_name" name="full_name" required type="text"></div>
                <div><label for="dnm_email"><?php echo esc_html( dnm_text( 'label_email', dnm_tr( array( 'pt' => 'Mail', 'es' => 'Correo', 'en' => 'Email', 'fr' => 'E-mail' ) ) ) ); ?></label><input id="dnm_email" name="email" required type="email"></div>
                <div><label for="dnm_phone"><?php echo esc_html( dnm_text( 'label_phone', dnm_tr( array( 'pt' => 'Contacto Telefónico', 'es' => 'Teléfono de Contacto', 'en' => 'Phone Number', 'fr' => 'Téléphone' ) ) ) ); ?></label><input id="dnm_phone" name="phone" required type="text"></div>
                <div><label for="dnm_business_name"><?php echo esc_html( dnm_text( 'label_business', dnm_tr( array( 'pt' => 'Nome do Negócio', 'es' => 'Nombre del Negocio', 'en' => 'Business Name', 'fr' => 'Nom de l’Entreprise' ) ) ) ); ?></label><input id="dnm_business_name" name="business_name" required type="text"></div>
                <div class="dnm-grid-1"><label for="dnm_location_country"><?php echo esc_html( dnm_text( 'label_location', dnm_tr( array( 'pt' => 'Localidade e País', 'es' => 'Ciudad y País', 'en' => 'City and Country', 'fr' => 'Ville et Pays' ) ) ) ); ?></label><input id="dnm_location_country" name="location_country" required type="text"></div>
                <div><label><?php echo esc_html( dnm_text( 'label_segment', dnm_tr( array( 'pt' => 'Em que segmento de mercado trabalha', 'es' => 'Segmento de mercado', 'en' => 'Market segment', 'fr' => 'Segment de marché' ) ) ) ); ?></label><div class="dnm-chipset"><input id="dnm_seg_mtm" type="checkbox" name="market_segments[]" value="Made to Measure"><label for="dnm_seg_mtm"><?php echo esc_html( dnm_text( 'label_mtm', 'Made to Measure' ) ); ?></label><input id="dnm_seg_rtw" type="checkbox" name="market_segments[]" value="Ready to Wear"><label for="dnm_seg_rtw"><?php echo esc_html( dnm_text( 'label_rtw', 'Ready to Wear' ) ); ?></label></div></div>
                <div><label><?php echo esc_html( dnm_text( 'label_gender', dnm_tr( array( 'pt' => 'Para que género produzem', 'es' => 'Género', 'en' => 'Gender', 'fr' => 'Genre' ) ) ) ); ?></label><div class="dnm-chipset"><input id="dnm_gen_h" type="checkbox" name="target_genders[]" value="Men"><label for="dnm_gen_h"><?php echo esc_html( dnm_text( 'label_men', dnm_tr( array( 'pt' => 'Homem', 'es' => 'Hombre', 'en' => 'Men', 'fr' => 'Homme' ) ) ) ); ?></label><input id="dnm_gen_m" type="checkbox" name="target_genders[]" value="Women"><label for="dnm_gen_m"><?php echo esc_html( dnm_text( 'label_women', dnm_tr( array( 'pt' => 'Mulher', 'es' => 'Mujer', 'en' => 'Women', 'fr' => 'Femme' ) ) ) ); ?></label></div></div>
                <div class="dnm-grid-1">
                    <label><?php echo esc_html( dnm_text( 'label_slot', dnm_tr( array( 'pt' => 'Data e horário (30 min)', 'es' => 'Fecha y horario (30 min)', 'en' => 'Date and time (30 min)', 'fr' => 'Date et horaire (30 min)' ) ) ) ); ?></label>
                    <?php if ( empty( $grouped ) ) : ?>
                        <p class="dnm-empty-slots"><?php echo esc_html( dnm_text( 'msg_all_booked', dnm_tr( array( 'pt' => 'Todas as datas e horários já estão completos.', 'es' => 'Todas las fechas y horarios ya están completos.', 'en' => 'All dates and times are fully booked.', 'fr' => 'Toutes les dates et horaires sont complets.' ) ) ) ); ?></p>
                    <?php else : ?>
                        <div class="dnm-tabs">
                            <?php $first = true; foreach ( $grouped as $day => $slots ) : ?>
                                <button type="button" class="dnm-tab <?php echo $first ? 'active' : ''; ?>" data-day="<?php echo esc_attr( $day ); ?>"><?php echo esc_html( $day_labels[ $day ] ?? $day ); ?></button>
                            <?php $first = false; endforeach; ?>
                        </div>
                        <?php $first = true; foreach ( $grouped as $day => $slots ) : ?>
                            <div class="dnm-slot-day <?php echo $first ? 'active' : ''; ?>" data-day-panel="<?php echo esc_attr( $day ); ?>"><div class="dnm-slots"><?php foreach ( $slots as $slot ) : $id = 'dnm_slot_' . md5( $slot['value'] ); ?><input id="<?php echo esc_attr( $id ); ?>" type="radio" name="slot_datetime" value="<?php echo esc_attr( $slot['value'] ); ?>" required><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $slot['label'] ); ?></label><?php endforeach; ?></div></div>
                        <?php $first = false; endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <button class="dnm-submit" type="submit" name="dnm_submit" value="1"><?php echo esc_html( dnm_text( 'label_book_button', dnm_tr( array( 'pt' => 'Agendar Reunião', 'es' => 'Reservar Reunión', 'en' => 'Book Meeting', 'fr' => 'Réserver une Réunion' ) ) ) ); ?></button>
        </form>
    </div>
    <?php
    return (string) ob_get_clean();
}

function dnm_handle_submission(): array {
    if ( ! isset( $_POST['dnm_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dnm_nonce'] ) ), 'dnm_book_meeting' ) ) {
        return array( 'ok' => false, 'message' => dnm_text( 'err_security', dnm_tr( array( 'pt' => 'Falha de segurança.', 'es' => 'Error de seguridad.', 'en' => 'Security check failed.', 'fr' => 'Échec de sécurité.' ) ) ) );
    }
    if ( ! dnm_is_human_submission() ) {
        return array( 'ok' => false, 'message' => dnm_text( 'err_validation', dnm_tr( array( 'pt' => 'Falha de validação.', 'es' => 'Error de validación.', 'en' => 'Validation failed.', 'fr' => 'Échec de validation.' ) ) ) );
    }
    if ( dnm_is_rate_limited() ) {
        return array( 'ok' => false, 'message' => dnm_text( 'err_rate_limited', dnm_tr( array( 'pt' => 'Muitas tentativas. Tente novamente em alguns minutos.', 'es' => 'Demasiados intentos. Inténtalo en unos minutos.', 'en' => 'Too many attempts. Please try again in a few minutes.', 'fr' => 'Trop de tentatives. Réessayez dans quelques minutes.' ) ) ) );
    }

    $full_name = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
    $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    $phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
    $business_name = sanitize_text_field( wp_unslash( $_POST['business_name'] ?? '' ) );
    $location_country = sanitize_text_field( wp_unslash( $_POST['location_country'] ?? '' ) );
    $slot_datetime = sanitize_text_field( wp_unslash( $_POST['slot_datetime'] ?? '' ) );
    $market_segments = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) ( $_POST['market_segments'] ?? array() ) ) );
    $target_genders = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) ( $_POST['target_genders'] ?? array() ) ) );
    $market_segments = array_values( array_intersect( $market_segments, array( 'Made to Measure', 'Ready to Wear' ) ) );
    $target_genders = array_values( array_intersect( $target_genders, array( 'Men', 'Women' ) ) );

    if ( ! $full_name || ! $email || ! $phone || ! $business_name || ! $location_country || ! $slot_datetime || empty( $market_segments ) || empty( $target_genders ) ) {
        return array( 'ok' => false, 'message' => dnm_text( 'err_required_fields', dnm_tr( array( 'pt' => 'Preencha todos os campos obrigatórios.', 'es' => 'Completa todos los campos obligatorios.', 'en' => 'Please fill all required fields.', 'fr' => 'Veuillez remplir tous les champs obligatoires.' ) ) ) );
    }

    $allowed = dnm_allowed_slots();
    if ( ! isset( $allowed[ $slot_datetime ] ) ) {
        return array( 'ok' => false, 'message' => dnm_text( 'err_invalid_slot', dnm_tr( array( 'pt' => 'Horário inválido.', 'es' => 'Horario inválido.', 'en' => 'Invalid slot.', 'fr' => 'Créneau invalide.' ) ) ) );
    }

    global $wpdb;
    $ok = $wpdb->insert(
        $wpdb->prefix . DNM_TABLE,
        array(
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'business_name' => $business_name,
            'location_country' => $location_country,
            'market_segments' => implode( ', ', $market_segments ),
            'target_genders' => implode( ', ', $target_genders ),
            'slot_datetime' => $slot_datetime,
            'created_at' => current_time( 'mysql' ),
        ),
        array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
    );

    if ( false === $ok ) {
        return array( 'ok' => false, 'message' => dnm_text( 'err_slot_taken', dnm_tr( array( 'pt' => 'Este horário já foi reservado.', 'es' => 'Ese horario ya fue reservado.', 'en' => 'This slot was already booked.', 'fr' => 'Ce créneau est déjà réservé.' ) ) ) );
    }

    dnm_send_emails(
        array(
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'business_name' => $business_name,
            'location_country' => $location_country,
            'market_segments' => implode( ', ', $market_segments ),
            'target_genders' => implode( ', ', $target_genders ),
            'slot_datetime' => $slot_datetime,
            'slot_label' => $allowed[ $slot_datetime ],
        )
    );

    return array( 'ok' => true, 'message' => dnm_text( 'msg_booking_confirmed', dnm_tr( array( 'pt' => 'Reserva confirmada.', 'es' => 'Reserva confirmada.', 'en' => 'Booking confirmed.', 'fr' => 'Réservation confirmée.' ) ) ) );
}

function dnm_is_human_submission(): bool {
    $honeypot = sanitize_text_field( wp_unslash( $_POST['website'] ?? '' ) );
    if ( '' !== $honeypot ) {
        return false;
    }

    $posted_ts = absint( $_POST['dnm_form_ts'] ?? 0 );
    if ( $posted_ts <= 0 ) {
        return false;
    }

    // Bots often submit instantly; require at least 3 seconds.
    if ( ( time() - $posted_ts ) < 3 ) {
        return false;
    }

    return true;
}

function dnm_is_rate_limited(): bool {
    $ip = dnm_get_request_ip();
    if ( '' === $ip ) {
        return false;
    }

    $key = 'dnm_rate_' . md5( $ip );
    $attempts = (int) get_transient( $key );
    $attempts++;
    set_transient( $key, $attempts, 10 * MINUTE_IN_SECONDS );

    return $attempts > 12;
}

function dnm_get_request_ip(): string {
    $remote = wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' );
    if ( is_string( $remote ) ) {
        $remote = trim( $remote );
    } else {
        $remote = '';
    }
    return filter_var( $remote, FILTER_VALIDATE_IP ) ? $remote : '';
}

function dnm_is_real_form_post(): bool {
    $required_scalar_keys = array( 'full_name', 'email', 'phone', 'business_name', 'location_country', 'slot_datetime' );
    foreach ( $required_scalar_keys as $key ) {
        if ( ! isset( $_POST[ $key ] ) || '' === trim( (string) wp_unslash( $_POST[ $key ] ) ) ) {
            return false;
        }
    }

    $segments = (array) ( $_POST['market_segments'] ?? array() );
    $genders  = (array) ( $_POST['target_genders'] ?? array() );
    if ( empty( $segments ) || empty( $genders ) ) {
        return false;
    }

    return true;
}
