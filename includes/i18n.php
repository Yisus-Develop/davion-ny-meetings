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

function dnm_default_ui_texts(): array {
    return array(
        'label_full_name' => dnm_tr( array( 'pt' => 'Nome e Apelido', 'es' => 'Nombre y Apellido', 'en' => 'First and Last Name', 'fr' => 'Nom et Prénom' ) ),
        'label_email' => dnm_tr( array( 'pt' => 'Mail', 'es' => 'Correo', 'en' => 'Email', 'fr' => 'E-mail' ) ),
        'label_phone' => dnm_tr( array( 'pt' => 'Contacto Telefónico', 'es' => 'Teléfono de Contacto', 'en' => 'Phone Number', 'fr' => 'Téléphone' ) ),
        'label_business' => dnm_tr( array( 'pt' => 'Nome do Negócio', 'es' => 'Nombre del Negocio', 'en' => 'Business Name', 'fr' => 'Nom de l’Entreprise' ) ),
        'label_location' => dnm_tr( array( 'pt' => 'Localidade e País', 'es' => 'Ciudad y País', 'en' => 'City and Country', 'fr' => 'Ville et Pays' ) ),
        'label_segment' => dnm_tr( array( 'pt' => 'Em que segmento de mercado trabalha', 'es' => 'Segmento de mercado', 'en' => 'Market segment', 'fr' => 'Segment de marché' ) ),
        'label_gender' => dnm_tr( array( 'pt' => 'Para que género produzem', 'es' => 'Género', 'en' => 'Gender', 'fr' => 'Genre' ) ),
        'label_slot' => dnm_tr( array( 'pt' => 'Data e horário (30 min)', 'es' => 'Fecha y horario (30 min)', 'en' => 'Date and time (30 min)', 'fr' => 'Date et horaire (30 min)' ) ),
        'label_book_button' => dnm_tr( array( 'pt' => 'Agendar Reunião', 'es' => 'Reservar Reunión', 'en' => 'Book Meeting', 'fr' => 'Réserver une Réunion' ) ),
        'label_mtm' => 'Made to Measure',
        'label_rtw' => 'Ready to Wear',
        'label_men' => dnm_tr( array( 'pt' => 'Homem', 'es' => 'Hombre', 'en' => 'Men', 'fr' => 'Homme' ) ),
        'label_women' => dnm_tr( array( 'pt' => 'Mulher', 'es' => 'Mujer', 'en' => 'Women', 'fr' => 'Femme' ) ),
        'msg_all_booked' => dnm_tr( array( 'pt' => 'Todas as datas e horários já estão completos.', 'es' => 'Todas las fechas y horarios ya están completos.', 'en' => 'All dates and times are fully booked.', 'fr' => 'Toutes les dates et horaires sont complets.' ) ),
        'err_security' => dnm_tr( array( 'pt' => 'Falha de segurança.', 'es' => 'Error de seguridad.', 'en' => 'Security check failed.', 'fr' => 'Échec de sécurité.' ) ),
        'err_validation' => dnm_tr( array( 'pt' => 'Falha de validação.', 'es' => 'Error de validación.', 'en' => 'Validation failed.', 'fr' => 'Échec de validation.' ) ),
        'err_rate_limited' => dnm_tr( array( 'pt' => 'Muitas tentativas. Tente novamente em alguns minutos.', 'es' => 'Demasiados intentos. Inténtalo en unos minutos.', 'en' => 'Too many attempts. Please try again in a few minutes.', 'fr' => 'Trop de tentatives. Réessayez dans quelques minutes.' ) ),
        'err_required_fields' => dnm_tr( array( 'pt' => 'Preencha todos os campos obrigatórios.', 'es' => 'Completa todos los campos obligatorios.', 'en' => 'Please fill all required fields.', 'fr' => 'Veuillez remplir tous les champs obligatoires.' ) ),
        'err_invalid_slot' => dnm_tr( array( 'pt' => 'Horário inválido.', 'es' => 'Horario inválido.', 'en' => 'Invalid slot.', 'fr' => 'Créneau invalide.' ) ),
        'err_slot_taken' => dnm_tr( array( 'pt' => 'Este horário já foi reservado.', 'es' => 'Ese horario ya fue reservado.', 'en' => 'This slot was already booked.', 'fr' => 'Ce créneau est déjà réservé.' ) ),
        'msg_booking_confirmed' => dnm_tr( array( 'pt' => 'Reserva confirmada.', 'es' => 'Reserva confirmada.', 'en' => 'Booking confirmed.', 'fr' => 'Réservation confirmée.' ) ),
    );
}

function dnm_text( string $key, string $fallback ): string {
    if ( function_exists( 'dnm_get_settings' ) ) {
        $settings = dnm_get_settings();
        if ( isset( $settings['ui_texts'][ $key ] ) ) {
            $value = trim( (string) $settings['ui_texts'][ $key ] );
            if ( '' !== $value ) {
                return $value;
            }
        }
    }
    return $fallback;
}
