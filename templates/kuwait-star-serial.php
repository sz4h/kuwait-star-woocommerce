<table class="kuwait-star-table">
    <tr>
        <th><?php _e( 'Share', SPWKS_TD ); ?></th>
        <th><?php _e( 'Code', SPWKS_TD ); ?></th>
        <th><?php _e( 'Copy', SPWKS_TD ); ?></th>
    </tr>
	<?php foreach ( $serials as $k => $serial ) : ?>
        <tr>
            <td><a href="#" onclick="shareKSCode('<?php echo $serial->SN_VALUE; ?>')"><i class='lar la-share-square'></i></a></td>
            <td>
                <label for="ks_code_<?php echo $item->get_id() . $k; ?>">
                    <input type="text" id="ks_code_<?php echo $item->get_id() . $k; ?>"
                           value="<?php echo $serial->SN_VALUE; ?>">
                </label>
            </td>
            <td>
                <a href="#" onclick="cpKSCode('ks_code_<?php echo $item->get_id() . $k; ?>'); return false;">
                    <i class='lar la-copy'></i>
                </a>
            </td>
        </tr>
        <tr>
            <td colspan="3"><?php _e( 'PIN' ); ?> : <?php echo $serial->PIN_VALUE; ?></td>
        </tr>
	<?php endforeach; ?>
</table>