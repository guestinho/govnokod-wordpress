            </div>

            <div id="footer">
                <address>
                    <span>&copy; 2008-2018 &laquo;Говнокод.ру&raquo;</span>
                    <span><a href="#">Обратная связь</a> | <a href="#">Лицензионное соглашение</a></span>
                </address>
            </div>
        </div>

        <?php wp_footer(); ?>

	</body>
</html>

<!--<?php echo get_num_queries(); ?> queries in <?php timer_stop(1); ?> seconds. -->

<!--
<?php if (current_user_can('administrator')) {
    global $wpdb;

    if (!empty($wpdb->queries)) {
        $queries_time = 0;
        foreach ($wpdb->queries as $query_info) {
            echo $query_info[0] . "\n";
            echo $query_info[1] . "\n";
            echo $query_info[2] . "\n";
            $queries_time += $query_info[1];
            echo "\n\n";
        }
        echo "Sum: $queries_time\n";
    }
} ?>
-->
