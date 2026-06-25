<?php
/**
 * Server-side render for pediment-child/availability-form.
 *
 * @var array $attributes
 */

$action_url = isset( $attributes['actionUrl'] ) ? (string) $attributes['actionUrl'] : '#';
$wrapper    = get_block_wrapper_attributes( array( 'class' => 'avail' ) );
?>
<form <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> action="<?php echo esc_url( $action_url ); ?>" aria-label="<?php esc_attr_e( 'Check availability', 'pediment-child' ); ?>">
	<div class="avail-field">
		<label for="arrival">
			<span class="avail-icon" aria-hidden="true"></span>
			<?php esc_html_e( 'Arrival', 'pediment-child' ); ?>
		</label>
		<input type="date" id="arrival" name="arrival">
	</div>
	<div class="avail-field">
		<label for="departure">
			<span class="avail-icon" aria-hidden="true"></span>
			<?php esc_html_e( 'Departure', 'pediment-child' ); ?>
		</label>
		<input type="date" id="departure" name="departure">
	</div>
	<div class="avail-field select-wrap">
		<label for="guests">
			<span class="avail-icon avail-icon--guest" aria-hidden="true"></span>
			<?php esc_html_e( 'Guests', 'pediment-child' ); ?>
		</label>
		<select id="guests" name="guests">
			<?php for ( $i = 1; $i <= 9; $i++ ) : ?>
				<option value="<?php echo esc_attr( (string) $i ); ?>" <?php selected( 2, $i ); ?>>
					<?php
					printf(
						/* translators: %d: number of guests. */
						esc_html( _n( '%d guest', '%d guests', $i, 'pediment-child' ) ),
						esc_html( (string) $i )
					);
					?>
				</option>
			<?php endfor; ?>
		</select>
	</div>
	<div class="avail-submit">
		<button type="submit" class="wc-btn wc-btn-yellow"><?php esc_html_e( 'Check availability', 'pediment-child' ); ?> <span class="arr" aria-hidden="true">→</span></button>
	</div>
</form>
