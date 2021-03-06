<?php

use Phinx\Migration\AbstractMigration;

// Load all the admin APIs, for convenience
require_once 'wordpress-api.php';
//--

class Sprint15 extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-donation' ) ) {
            deactivate_plugins( $plugin->file, '' );
        }

        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-credits' ) ) {
            activate_plugin( $plugin->file, '' );
        }

        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'new-royalslider' ) ) {
            activate_plugin( $plugin->file, '' );
        }

        $id = wp_insert_post( [
            'post_type'     => 'page',
            'post_status'   => 'publish',
            'post_title'    => 'Purchase credits'
        ], true );

        update_post_meta( $id, '_wp_page_template', 'purchase-credits.php' );

        $id = wp_insert_post( [
            'post_type'     => 'page',
            'post_status'   => 'publish',
            'post_title'    => 'Pay with BitPay'
        ], true );

        update_post_meta( $id, '_wp_page_template', 'purchase-credits-step2.php' );

        // create the orders table
        $table = $this->table('orders');
        $table
              ->addColumn('order_number', 'string', array('limit' => 50, 'null' => false))
              ->addColumn('user_id', 'integer')
              ->addColumn('invoice_id', 'string')
              ->addColumn('url', 'string')
              ->addColumn('status', 'enum', ['values' => ['new','completed']])
              ->addColumn('btc_price', 'float')
              ->addColumn('btc_rate', 'float')
              ->addColumn('price', 'float')
              ->addIndex(array('order_number'), array('unique' => true))
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-donation' ) ) {
            activate_plugin( $plugin->file, '' );
        }

        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-credits' ) ) {
            deactivate_plugins( $plugin->file, '' );
        }

        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'new-royalslider' ) ) {
            deactivate_plugins( $plugin->file, '' );
        }

        if ($this->hasTable('orders')) {
            // remove the orders table
            $this->dropTable('orders');
        }
    }
}