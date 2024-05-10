<?php global $logs; ?>
<div class="wrap">
    <a href="<?php echo admin_url( 'admin.php?page=kuwait_star_logs&clear=1' ); ?>"
       class='page-title-action'><?php _e( 'Clear logs', SPWKS_TD ); ?></a>
    <ul class='log-wrapper'>
		<?php foreach ( $logs as $log ) : ?>
            <li class="log-item">
				<?php echo nl2br( $log['item'] ); ?>
                <textarea
                        class="data-json"><?php echo json_encode( unserialize( str_replace( '\"', '"', $log['data'] ) ), JSON_PRETTY_PRINT ); ?></textarea>
            </li>
		<?php endforeach; ?>
    </ul>
</div>
<style>
    .log-wrapper {
        margin: 1rem;
    }
    .log-item {
        background: #fff;
        -webkit-box-shadow: 0 0 2px 0px rgba(0, 0, 0, 0.1);
        -moz-box-shadow: 0 0 2px 0px rgba(0, 0, 0, 0.1);
        box-shadow: 0 0 2px 0px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
        border-radius: 0.5rem;
        padding: 0.5rem;
    }
    .log-item:nth-child(even) {
        background: #f9f9f9;
    }

    .label {
        display: inline-block;
        max-width: 200px;
        width: 30%;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }

    .date {
        text-align: center;
        max-width: 100%;
        width: 100%;
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }
    .success {
        color: #0B613C;
    }
    .error {
        color: #9e1313;
    }

    .data-json {
        width: 100%;
        height: 150px;
        direction: ltr;
    }
</style>