<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'dnm_admin_menu' );
add_action( 'admin_init', 'dnm_register_settings' );

function dnm_admin_menu(): void {
    add_options_page( 'EWEB Smart Meetings', 'EWEB Meetings', 'manage_options', 'dnm-settings', 'dnm_render_settings_page' );
}

function dnm_register_settings(): void {
    register_setting(
        'dnm_settings_group',
        'dnm_settings',
        array(
            'type' => 'array',
            'sanitize_callback' => 'dnm_sanitize_settings',
            'default' => array(),
        )
    );
}

function dnm_sanitize_settings( $input ): array {
    $input = is_array( $input ) ? $input : array();

    $admin_recipients = sanitize_text_field( $input['admin_recipients'] ?? '' );
    $emails = array_filter( array_map( 'trim', explode( ',', $admin_recipients ) ) );
    $emails = array_values( array_filter( $emails, 'is_email' ) );

    return array(
        'admin_recipients' => implode( ', ', $emails ),
        'from_name' => sanitize_text_field( $input['from_name'] ?? '' ),
        'from_email' => sanitize_email( $input['from_email'] ?? '' ),
        'subject_client' => sanitize_text_field( $input['subject_client'] ?? '' ),
        'subject_admin' => sanitize_text_field( $input['subject_admin'] ?? '' ),
        'body_client' => wp_kses_post( $input['body_client'] ?? '' ),
        'body_admin' => wp_kses_post( $input['body_admin'] ?? '' ),
        'success_message' => sanitize_text_field( $input['success_message'] ?? '' ),
    );
}

function dnm_get_settings(): array {
    $defaults = array(
        'admin_recipients' => get_option( 'admin_email' ),
        'from_name' => get_bloginfo( 'name' ),
        'from_email' => get_option( 'admin_email' ),
        'subject_client' => 'Meeting Confirmation - EWEB',
        'subject_admin' => 'New meeting booked - EWEB',
        'body_client' => "Your meeting has been confirmed.\n\n{summary}",
        'body_admin' => "New meeting booked:\n\n{summary}",
        'success_message' => 'Booking confirmed. We have sent confirmation by email.',
    );

    $saved = get_option( 'dnm_settings', array() );
    if ( ! is_array( $saved ) ) {
        $saved = array();
    }

    return wp_parse_args( $saved, $defaults );
}

function dnm_render_settings_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    global $wpdb;
    $s = dnm_get_settings();
    $table = $wpdb->prefix . DNM_TABLE;

    if ( isset( $_POST['dnm_delete_leads'] ) && isset( $_POST['dnm_delete_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dnm_delete_nonce'] ) ), 'dnm_delete_leads' ) ) {
        $ids = array_map( 'absint', (array) ( $_POST['lead_ids'] ?? array() ) );
        $ids = array_values( array_filter( $ids ) );
        if ( ! empty( $ids ) ) {
            $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
            $sql = $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ($placeholders)", $ids ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            if ( $sql ) {
                $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                echo '<div class="notice notice-success"><p>Leads seleccionados eliminados.</p></div>';
            }
        } else {
            echo '<div class="notice notice-warning"><p>No seleccionaste leads para eliminar.</p></div>';
        }
    }

    $leads = $wpdb->get_results( "SELECT id, full_name, email, phone, business_name, location_country, market_segments, target_genders, slot_datetime, created_at FROM {$table} ORDER BY created_at DESC LIMIT 200", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    ?>
    <div class="wrap">
        <h1>EWEB Smart Meetings Scheduler</h1>
        <p>Tokens disponibles para plantillas: <code>{full_name}</code>, <code>{email}</code>, <code>{phone}</code>, <code>{business_name}</code>, <code>{location_country}</code>, <code>{market_segments}</code>, <code>{target_genders}</code>, <code>{slot_label}</code>, <code>{summary}</code>, <code>{summary_table}</code>.</p>

        <form method="post" action="options.php">
            <?php settings_fields( 'dnm_settings_group' ); ?>
            <table class="form-table" role="presentation">
                <tr><th scope="row"><label for="dnm_admin_recipients">Correos destino (admin)</label></th><td><input name="dnm_settings[admin_recipients]" id="dnm_admin_recipients" type="text" class="regular-text" value="<?php echo esc_attr( $s['admin_recipients'] ); ?>" /><p class="description">Separados por coma.</p></td></tr>
                <tr><th scope="row"><label for="dnm_from_name">Nombre remitente</label></th><td><input name="dnm_settings[from_name]" id="dnm_from_name" type="text" class="regular-text" value="<?php echo esc_attr( $s['from_name'] ); ?>" /></td></tr>
                <tr><th scope="row"><label for="dnm_from_email">Email remitente</label></th><td><input name="dnm_settings[from_email]" id="dnm_from_email" type="email" class="regular-text" value="<?php echo esc_attr( $s['from_email'] ); ?>" /></td></tr>
                <tr><th scope="row"><label for="dnm_subject_client">Asunto cliente</label></th><td><input name="dnm_settings[subject_client]" id="dnm_subject_client" type="text" class="regular-text" value="<?php echo esc_attr( $s['subject_client'] ); ?>" /></td></tr>
                <tr><th scope="row"><label for="dnm_subject_admin">Asunto admin</label></th><td><input name="dnm_settings[subject_admin]" id="dnm_subject_admin" type="text" class="regular-text" value="<?php echo esc_attr( $s['subject_admin'] ); ?>" /></td></tr>
                <tr><th scope="row"><label for="dnm_body_client">Mensaje cliente</label></th><td><textarea name="dnm_settings[body_client]" id="dnm_body_client" rows="6" class="large-text"><?php echo esc_textarea( $s['body_client'] ); ?></textarea></td></tr>
                <tr><th scope="row"><label for="dnm_body_admin">Mensaje admin</label></th><td><textarea name="dnm_settings[body_admin]" id="dnm_body_admin" rows="6" class="large-text"><?php echo esc_textarea( $s['body_admin'] ); ?></textarea></td></tr>
                <tr><th scope="row"><label for="dnm_success_message">Mensaje éxito formulario</label></th><td><input name="dnm_settings[success_message]" id="dnm_success_message" type="text" class="regular-text" value="<?php echo esc_attr( $s['success_message'] ); ?>" /></td></tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <hr>
        <h2>Leads guardados (últimos 200)</h2>
        <form method="post">
            <?php wp_nonce_field( 'dnm_delete_leads', 'dnm_delete_nonce' ); ?>
            <p>
                <button type="submit" name="dnm_delete_leads" value="1" class="button button-secondary" onclick="return confirm('¿Eliminar leads seleccionados? Esta acción no se puede deshacer.');">
                    Eliminar seleccionados
                </button>
            </p>
        <table class="widefat striped">
            <thead><tr><th>ID</th><th>Fecha</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Negocio</th><th>Ubicación</th><th>Segmento</th><th>Género</th><th>Slot</th></tr></thead>
            <tbody>
            <?php if ( empty( $leads ) ) : ?>
                <tr><td colspan="10">Sin registros todavía.</td></tr>
            <?php else : foreach ( $leads as $lead ) : ?>
                <tr>
                    <td><label><input type="checkbox" name="lead_ids[]" value="<?php echo esc_attr( (string) $lead['id'] ); ?>"> <?php echo esc_html( $lead['id'] ); ?></label></td>
                    <td><?php echo esc_html( $lead['created_at'] ); ?></td>
                    <td><?php echo esc_html( $lead['full_name'] ); ?></td>
                    <td><?php echo esc_html( $lead['email'] ); ?></td>
                    <td><?php echo esc_html( $lead['phone'] ); ?></td>
                    <td><?php echo esc_html( $lead['business_name'] ); ?></td>
                    <td><?php echo esc_html( $lead['location_country'] ); ?></td>
                    <td><?php echo esc_html( $lead['market_segments'] ); ?></td>
                    <td><?php echo esc_html( $lead['target_genders'] ); ?></td>
                    <td><?php echo esc_html( $lead['slot_datetime'] ); ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </form>
    </div>
    <?php
}
