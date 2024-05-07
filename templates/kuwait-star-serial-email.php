<table class="kuwait-star-table <?php echo $wrapper_class; ?>">
    <tr>
        <th><?php _e( 'Code', SPWKS_TD ); ?></th>
    </tr>
	<?php foreach ( $serials as $k => $serial ) : ?>
        <tr>
            <td>
                <label for="ks_code_<?php echo $item->get_id() . $k; ?>">
                    <input type="text" id="ks_code_<?php echo $item->get_id() . $k; ?>"
                           value="<?php echo $serial->SN_VALUE; ?>">
                </label>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'PIN' ); ?> : <?php echo $serial->PIN_VALUE; ?></td>
        </tr>
	<?php endforeach; ?>
</table>