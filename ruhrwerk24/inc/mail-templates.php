<?php
/**
 * E-Mails zu einer Anfrage: Benachrichtigung an den Betrieb, Bestätigung an den Kunden.
 *
 * Inline-Styles und Tabellen, weil Outlook kein modernes CSS kann.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * HTML-Rahmen für alle Mails.
 *
 * @param string $title Überschrift.
 * @param string $body  Bereits escapter HTML-Inhalt.
 */
function rw24_mail_layout( string $title, string $body ): string {
	$navy   = sanitize_hex_color( rw24_contact( 'rw_color_primary' ) ) ?: '#0A2342';
	$accent = sanitize_hex_color( rw24_contact( 'rw_color_accent' ) ) ?: '#F4651A';

	$footer = implode(
		' · ',
		array_filter(
			array(
				esc_html( rw24_contact( 'rw_owner' ) ),
				esc_html( rw24_contact( 'rw_street' ) ),
				esc_html( trim( rw24_contact( 'rw_zip' ) . ' ' . rw24_contact( 'rw_city' ) ) ),
				'<a href="' . esc_url( rw24_phone_href() ) . '" style="color:#6b7686">' . esc_html( rw24_contact( 'rw_phone' ) ) . '</a>',
			)
		)
	);

	return '<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>' . esc_html( $title ) . '</title></head>'
		. '<body style="margin:0;padding:0;background:#f2f4f7;font-family:Arial,Helvetica,sans-serif;color:#1b2533">'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f4f7"><tr><td align="center" style="padding:24px 12px">'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden">'
		. '<tr><td style="background:' . esc_attr( $navy ) . ';padding:22px 28px;font-size:22px;font-weight:bold;color:#ffffff">' . esc_html( get_bloginfo( 'name' ) ) . '<span style="display:block;width:40px;height:3px;margin-top:10px;background:' . esc_attr( $accent ) . '"></span></td></tr>'
		. '<tr><td style="padding:28px">'
		. '<h1 style="margin:0 0 16px;font-size:20px;line-height:1.3;color:' . esc_attr( $navy ) . '">' . esc_html( $title ) . '</h1>'
		. $body
		. '</td></tr>'
		. '<tr><td style="padding:18px 28px;background:#f7f8fa;font-size:12px;line-height:1.6;color:#6b7686">' . $footer . '</td></tr>'
		. '</table></td></tr></table></body></html>';
}

/**
 * Tabelle mit den Angaben einer Anfrage.
 */
function rw24_mail_table( int $lead_id ): string {
	$html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;line-height:1.5">';

	foreach ( rw24_lead_rows( $lead_id ) as $label => $value ) {
		if ( '' === $value ) {
			continue;
		}

		$html .= '<tr><td style="padding:8px 12px 8px 0;border-top:1px solid #e6e9ee;color:#6b7686;width:140px;vertical-align:top">' . esc_html( $label ) . '</td>'
			. '<td style="padding:8px 0;border-top:1px solid #e6e9ee;vertical-align:top">' . nl2br( esc_html( $value ) ) . '</td></tr>';
	}

	return $html . '</table>';
}

/**
 * Beide Mails versenden.
 *
 * Fehler beim Versand werden protokolliert, brechen aber nichts ab - die
 * Anfrage liegt bereits im Backend.
 */
function rw24_send_lead_mails( int $lead_id ): void {
	$d   = rw24_lead_data( $lead_id );
	$ref = (string) get_post_meta( $lead_id, '_rw_ref', true );

	$recipient = rw24_contact( 'rw_lead_recipient' );
	$recipient = is_email( $recipient ) ? $recipient : rw24_contact( 'rw_email' );
	$recipient = is_email( $recipient ) ? $recipient : get_option( 'admin_email' );

	$html = array( 'Content-Type: text/html; charset=UTF-8' );

	// An den Betrieb: Antworten gehen direkt an den Kunden.
	$urgent  = 'notfall' === ( $d['urgency'] ?? '' );
	$subject = sprintf(
		'%s%s – %s (%s)',
		$urgent ? '[NOTFALL] ' : '',
		$ref,
		rw24_choice_label( 'service', $d['service'] ?? '' ),
		trim( ( $d['zip'] ?? '' ) . ' ' . ( $d['place'] ?? '' ) )
	);

	$body = '<p style="margin:0 0 18px;font-size:15px;line-height:1.6">' . esc_html__( 'Über die Website ist eine neue Anfrage eingegangen.', 'ruhrwerk24' ) . '</p>'
		. rw24_mail_table( $lead_id );

	if ( ! empty( $d['phone'] ) ) {
		$body .= '<p style="margin:24px 0 0"><a href="' . esc_url( rw24_phone_href( $d['phone'] ) ) . '" style="display:inline-block;padding:12px 22px;border-radius:8px;background:#F4651A;color:#ffffff;text-decoration:none;font-weight:bold">' . esc_html__( 'Jetzt zurückrufen', 'ruhrwerk24' ) . '</a></p>';
	}

	$body .= '<p style="margin:18px 0 0;font-size:13px"><a href="' . esc_url( admin_url( 'post.php?post=' . $lead_id . '&action=edit' ) ) . '" style="color:#0A2342">' . esc_html__( 'Im Backend öffnen', 'ruhrwerk24' ) . '</a></p>';

	$admin_headers = $html;
	if ( ! empty( $d['email'] ) ) {
		$admin_headers[] = 'Reply-To: ' . str_replace( array( "\r", "\n", '<', '>' ), '', $d['name'] ) . ' <' . $d['email'] . '>';
	}

	if ( ! wp_mail( $recipient, $subject, rw24_mail_layout( __( 'Neue Anfrage', 'ruhrwerk24' ) . ' ' . $ref, $body ), $admin_headers ) ) {
		error_log( 'Ruhrwerk24: Benachrichtigung zu ' . $ref . ' konnte nicht gesendet werden.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	// An den Kunden: Bestätigung mit Anfragenummer.
	if ( empty( $d['email'] ) ) {
		return;
	}

	$first = strtok( (string) $d['name'], ' ' );

	$body = '<p style="margin:0 0 14px;font-size:15px;line-height:1.6">' . esc_html(
		sprintf(
			/* translators: %s: Vorname. */
			__( 'Hallo %s,', 'ruhrwerk24' ),
			$first ? $first : $d['name']
		)
	) . '</p>'
		. '<p style="margin:0 0 14px;font-size:15px;line-height:1.6">' . esc_html__( 'vielen Dank für Ihre Anfrage. Wir haben alles erhalten und melden uns in der Regel innerhalb weniger Stunden – werktags oft deutlich schneller.', 'ruhrwerk24' ) . '</p>'
		. '<p style="margin:0 0 22px;padding:14px 18px;border-radius:8px;background:#f2f4f7;font-size:15px">' . esc_html__( 'Ihre Anfragenummer:', 'ruhrwerk24' ) . ' <strong style="font-size:18px;letter-spacing:.5px">' . esc_html( $ref ) . '</strong></p>'
		. rw24_mail_table( $lead_id )
		. '<p style="margin:22px 0 0;font-size:15px;line-height:1.6">' . esc_html__( 'Ist es dringend? Dann rufen Sie uns bitte direkt an:', 'ruhrwerk24' ) . ' <a href="' . esc_url( rw24_phone_href() ) . '" style="color:#0A2342;font-weight:bold">' . esc_html( rw24_contact( 'rw_phone' ) ) . '</a></p>';

	$customer_headers   = $html;
	$customer_headers[] = 'Reply-To: ' . get_bloginfo( 'name' ) . ' <' . $recipient . '>';

	wp_mail(
		$d['email'],
		/* translators: %s: Anfragenummer. */
		sprintf( __( 'Ihre Anfrage %s ist bei uns eingegangen', 'ruhrwerk24' ), $ref ),
		rw24_mail_layout( __( 'Danke für Ihre Anfrage', 'ruhrwerk24' ), $body ),
		$customer_headers
	);
}
