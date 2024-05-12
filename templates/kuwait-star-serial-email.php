<table class="kuwait-star-table <?php echo $wrapper_class; ?>" style="text-align:center;margin: 0 auto;">
    <tr>
        <th style='padding:0;'><?php _e( 'Code', SPWKS_TD ); ?></th>
    </tr>
	<?php foreach ( $serials as $k => $serial ) : ?>
        <tr>
            <td style="padding:0;">
                <label for="ks_code_<?php echo $item->get_id() . $k; ?>">
                    <input type="text" id="ks_code_<?php echo $item->get_id() . $k; ?>" readonly="readonly"
                           value="<?php echo $serial->SN_VALUE; ?>" style="color: #0b2e8d;text-align: center;">
                </label>
            </td>
        </tr>
        <tr>
            <td style="font-size: 90%;padding: 0;"><?php _e( 'PIN' ); ?> : <?php echo $serial->PIN_VALUE; ?></td>
        </tr>
	<?php endforeach; ?>
</table>